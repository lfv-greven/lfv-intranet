<x-filament-panels::page>
    <div class="space-y-6">
        <section class="space-y-3">
            <div class="flex items-center gap-2">
                <div class="h-2 w-2 rounded-full bg-primary-500"></div>
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Mattermost</h2>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Aktionen für unseren Mattermost-Server.
            </p>

            <div class="grid gap-4 lg:grid-cols-2">
                <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Passwort zurücksetzen</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Sendet einen Reset-Link an die E-Mail des ausgewählten Vereinsflieger-Mitglieds.
                            </p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                            Aktiv
                        </span>
                    </div>

                    <div class="mt-4 rounded-xl bg-gray-50 p-3 dark:bg-gray-800/60">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Wann nutzen?</p>
                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                            Wenn ein Mitglied keinen Zugriff mehr auf Mattermost hat und ein neues Passwort benötigt.
                        </p>
                    </div>

                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-200">
                        Aus Sicherheitsgründen wird immer nur ein Reset-Link per E-Mail verschickt.
                    </div>

                    <div class="mt-4">
                        {{ $this->sendMattermostPasswordResetAction }}
                    </div>
                </article>
            </div>
        </section>
    </div>
</x-filament-panels::page>
