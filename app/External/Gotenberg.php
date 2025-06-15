<?php

namespace App\External;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Gotenberg
{
    public function __construct(
        private $url,
        private $username,
        private $password,
    ) {}

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->url)
            ->withBasicAuth($this->username, $this->password);
    }

    public function htmlToPdf($html): string
    {
        $request = $this->request();

        $request->attach('index.html', $html, 'index.html');

        $res = $request->post('forms/chromium/convert/html', [
            'preferCssPageSize' => true,
        ]);

        return $res->body();
    }

    public function merge(array $files): string
    {
        $request = $this->request();

        foreach ($files as $i => $file) {
            $request = $request->attach("$i.pdf", $file, "$i.pdf");
        }

        $res = $request->post('forms/pdfengines/merge', []);

        return $res->body();
    }
}
