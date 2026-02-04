<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeEwsService
{
    public function getCalendarEvents(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $url = config('services.ews.url');
        $username = config('services.ews.username');
        $password = config('services.ews.password');
        $version = config('services.ews.version', 'Exchange2010_SP2');
        $verifySsl = config('services.ews.verify_ssl', true);
        $authType = config('services.ews.auth', 'basic');

        if (empty($url) || empty($username) || empty($password)) {
            return ['error' => 'EWS ayarları eksik. Lütfen .env dosyasını kontrol edin.'];
        }

        $soap = $this->buildFindItemRequest($start, $end, $version);

        $response = $authType === 'ntlm'
            ? Http::withOptions(['auth' => [$username, $password, 'ntlm'], 'verify' => $verifySsl])
            : Http::withBasicAuth($username, $password)->withOptions(['verify' => $verifySsl]);

        $response = $response
            ->withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => 'http://schemas.microsoft.com/exchange/services/2006/messages/FindItem',
            ])
            ->send('POST', $url, ['body' => $soap]);

        if (!$response->successful()) {
            return ['error' => 'EWS isteği başarısız. HTTP ' . $response->status()];
        }

        $body = $response->body();
        if (stripos($body, '<soap:Envelope') === false && stripos($body, '<s:Envelope') === false) {
            Log::error('EWS SOAP dönmedi. İlk 500 karakter:', [
                'snippet' => substr($body, 0, 500),
            ]);
            return ['error' => 'EWS SOAP cevabı gelmedi. HTML/redirect olabilir. EWS URL ve NTLM yetkisini kontrol edin.'];
        }

        return $this->parseFindItemResponse($body);
    }

    private function buildFindItemRequest(\DateTimeInterface $start, \DateTimeInterface $end, string $version): string
    {
        $startIso = $start->format('c');
        $endIso = $end->format('c');

        return <<<XML
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:t="http://schemas.microsoft.com/exchange/services/2006/types"
    xmlns:m="http://schemas.microsoft.com/exchange/services/2006/messages">
    <soap:Header>
        <t:RequestServerVersion Version="{$version}" />
    </soap:Header>
    <soap:Body>
        <m:FindItem Traversal="Shallow">
            <m:ItemShape>
                <t:BaseShape>IdOnly</t:BaseShape>
                <t:AdditionalProperties>
                    <t:FieldURI FieldURI="item:Subject" />
                    <t:FieldURI FieldURI="calendar:Start" />
                    <t:FieldURI FieldURI="calendar:End" />
                    <t:FieldURI FieldURI="calendar:Location" />
                    <t:FieldURI FieldURI="calendar:Organizer" />
                </t:AdditionalProperties>
            </m:ItemShape>
            <m:CalendarView StartDate="{$startIso}" EndDate="{$endIso}" />
            <m:ParentFolderIds>
                <t:DistinguishedFolderId Id="calendar" />
            </m:ParentFolderIds>
        </m:FindItem>
    </soap:Body>
</soap:Envelope>
XML;
    }

    private function parseFindItemResponse(string $xmlString): array
    {
        $xml = @simplexml_load_string($xmlString);
        if (!$xml) {
            Log::error('EWS XML parse edilemedi. İlk 500 karakter:', [
                'snippet' => substr($xmlString, 0, 500),
            ]);
            return ['error' => 'EWS cevabı parse edilemedi. Sunucu geçerli SOAP/XML dönmüyor olabilir.'];
        }

        // Namespace prefix değişebildiği için local-name ile yakala
        $items = $xml->xpath('//*[local-name()="CalendarItem"]') ?: [];

        $events = [];
        foreach ($items as $item) {
            $events[] = [
                'subject' => (string)($item->Subject ?? ''),
                'start' => (string)($item->Start ?? ''),
                'end' => (string)($item->End ?? ''),
                'location' => (string)($item->Location ?? ''),
                'organizer' => (string)($item->Organizer->Mailbox->EmailAddress ?? ''),
            ];
        }

        return ['events' => $events];
    }
}
