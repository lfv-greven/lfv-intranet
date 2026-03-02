<?php

namespace App\Filament\Pages;

use App\External\Mattermost;
use App\Services\VereinsfliegerUsers;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Pages\PageConfiguration;
use Illuminate\Support\Str;

/**
 * @extends Page<PageConfiguration>
 */
class Tools extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Tools';

    protected static string|\UnitEnum|null $navigationGroup = 'Integrationen';

    protected static ?int $navigationSort = 100;

    protected static ?string $title = 'Admin Tools';

    protected string $view = 'filament.pages.tools';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() === true;
    }

    public function sendMattermostPasswordResetAction(): Action
    {
        return Action::make('sendMattermostPasswordReset')
            ->label('Reset-Link senden')
            ->icon('heroicon-o-key')
            ->color('primary')
            ->extraAttributes([
                'class' => 'w-full justify-center',
            ])
            ->modalHeading('Mattermost Passwort-Reset senden')
            ->modalDescription('Wähle ein Mitglied aus. Der Reset-Link wird an die im Vereinsflieger hinterlegte E-Mail-Adresse gesendet.')
            ->modalWidth('4xl')
            ->modalSubmitActionLabel('Reset-Link senden')
            ->form([
                Select::make('vf_member_id')
                    ->label('Mitglied (Vereinsflieger)')
                    ->required()
                    ->searchable()
                    ->placeholder('Name, E-Mail oder Mitgliedsnummer suchen ...')
                    ->getSearchResultsUsing(fn (string $search): array => $this->searchVfMembers($search))
                    ->getOptionLabelUsing(fn ($value): ?string => $this->getVfMemberLabel($value)),
            ])
            ->requiresConfirmation()
            ->action(function (array $data): void {
                $memberId = (int) ($data['vf_member_id'] ?? 0);
                $vfUser = app(VereinsfliegerUsers::class)->findByMemberId($memberId);
                $email = trim((string) data_get($vfUser, 'email', ''));

                if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Notification::make()
                        ->danger()
                        ->title('Keine gültige Vereinsflieger-E-Mail gefunden')
                        ->body('Für das ausgewählte Mitglied ist keine gültige E-Mail-Adresse hinterlegt.')
                        ->send();

                    return;
                }

                $success = Mattermost::requestPasswordReset($email);

                if (! $success) {
                    Notification::make()
                        ->danger()
                        ->title('Passwort-Reset fehlgeschlagen')
                        ->body('Der Reset konnte für '.$email.' nicht ausgelöst werden.')
                        ->send();

                    return;
                }

                Notification::make()
                    ->success()
                    ->title('Passwort-Reset ausgelöst')
                    ->body('Für '.$email.' wurde ein Mattermost-Passwort-Reset versendet.')
                    ->send();
            });
    }

    private function searchVfMembers(string $search): array
    {
        $needle = Str::of($search)->squish()->lower()->toString();

        if (blank($needle)) {
            return [];
        }

        return collect(app(VereinsfliegerUsers::class)->all())
            ->filter(function (array $user) use ($needle): bool {
                $haystack = collect([
                    data_get($user, 'memberid'),
                    data_get($user, 'firstname'),
                    data_get($user, 'lastname'),
                    data_get($user, 'email'),
                ])
                    ->filter(fn (mixed $value): bool => filled($value))
                    ->implode(' ');

                return filled($haystack)
                    && Str::of($haystack)->lower()->contains($needle);
            })
            ->sortBy(fn (array $user): string => collect([
                data_get($user, 'lastname', ''),
                data_get($user, 'firstname', ''),
                data_get($user, 'memberid', ''),
            ])->implode(' '))
            ->take(50)
            ->mapWithKeys(function (array $user): array {
                $memberId = (string) data_get($user, 'memberid', '');

                if (blank($memberId)) {
                    return [];
                }

                return [$memberId => $this->formatVfMemberLabel($user)];
            })
            ->all();
    }

    private function getVfMemberLabel(mixed $value): ?string
    {
        $memberId = (int) $value;

        if ($memberId <= 0) {
            return null;
        }

        $user = app(VereinsfliegerUsers::class)->findByMemberId($memberId);

        return $user ? $this->formatVfMemberLabel($user) : null;
    }

    private function formatVfMemberLabel(array $user): string
    {
        $memberId = (string) data_get($user, 'memberid', '-');
        $firstname = trim((string) data_get($user, 'firstname', ''));
        $lastname = trim((string) data_get($user, 'lastname', ''));
        $email = trim((string) data_get($user, 'email', 'keine E-Mail'));

        return sprintf('%s %s (#%s) - %s', $firstname, $lastname, $memberId, $email);
    }
}
