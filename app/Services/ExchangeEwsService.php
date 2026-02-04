<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ExchangeEwsService
{
    public function getCalendarEvents(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $url = config('services.ews.url');
        $username = config('services.ews.username');
        $password = config('services.ews.password');
        $version = config('services.ews.version', 'Exchange2010_SP2');
        $verifySsl = config('services.ews.verify_ssl', true);

        if (empty($url) || empty($username) || empty($password)) {
            return ['error' => 'EWS ayarları eksik. Lütfen .env dosyasını kontrol edin.'];
        }

        $soap = $this->buildFindItemRequest($start, $end, $version);

        $response = Http::withBasicAuth($username, $password)
            ->withOptions(['verify' => $verifySsl])
            ->withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => 'http://schemas.microsoft.com/exchange/services/2006/messages/FindItem',
            ])
            ->send('POST', $url, ['body' => $soap]);

        if (!$response->successful()) {
            return ['error' => 'EWS isteği başarısız. HTTP ' . $response->status()];
        }

        return $this->parseFindItemResponse($response->body());
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
            return ['error' => 'EWS cevabı parse edilemedi.'];
        }

        $xml->registerXPathNamespace('t', 'http://schemas.microsoft.com/exchange/services/2006/types');
        $items = $xml->xpath('//t:CalendarItem') ?: [];

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
