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

        $response = $this->sendEwsRequest(
            $soap,
            'http://schemas.microsoft.com/exchange/services/2006/messages/FindItem',
            $url,
            $username,
            $password,
            $verifySsl,
            $authType
        );

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

        $result = $this->parseFindItemResponse($body);
        if (isset($result['error'])) {
            return $result;
        }

        // Body boşsa GetItem ile doldur
        $events = $result['events'];
        foreach ($events as &$event) {
            if (!empty($event['body']) || empty($event['item_id'])) {
                continue;
            }
            $body = $this->getItemBody(
                $event['item_id'],
                $event['change_key'] ?? '',
                $version,
                $url,
                $username,
                $password,
                $verifySsl,
                $authType
            );
            if (is_string($body) && $body !== '') {
                $event['body'] = $body;
            }
        }

        return ['events' => $events];
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
                    <t:FieldURI FieldURI="item:Body" />
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

    private function buildGetItemRequest(string $itemId, string $changeKey, string $version): string
    {
        $itemId = htmlspecialchars($itemId, ENT_QUOTES, 'UTF-8');
        $changeKey = htmlspecialchars($changeKey, ENT_QUOTES, 'UTF-8');

        return <<<XML
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:t="http://schemas.microsoft.com/exchange/services/2006/types"
    xmlns:m="http://schemas.microsoft.com/exchange/services/2006/messages">
    <soap:Header>
        <t:RequestServerVersion Version="{$version}" />
    </soap:Header>
    <soap:Body>
        <m:GetItem>
            <m:ItemShape>
                <t:BaseShape>IdOnly</t:BaseShape>
                <t:BodyType>Text</t:BodyType>
                <t:AdditionalProperties>
                    <t:FieldURI FieldURI="item:Body" />
                </t:AdditionalProperties>
            </m:ItemShape>
            <m:ItemIds>
                <t:ItemId Id="{$itemId}" ChangeKey="{$changeKey}" />
            </m:ItemIds>
        </m:GetItem>
    </soap:Body>
</soap:Envelope>
XML;
    }

    private function sendEwsRequest(
        string $soap,
        string $action,
        string $url,
        string $username,
        string $password,
        bool $verifySsl,
        string $authType
    ) {
        $client = $authType === 'ntlm'
            ? Http::withOptions(['auth' => [$username, $password, 'ntlm'], 'verify' => $verifySsl])
            : Http::withBasicAuth($username, $password)->withOptions(['verify' => $verifySsl]);

        return $client
            ->withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => $action,
            ])
            ->send('POST', $url, ['body' => $soap]);
    }

    private function getItemBody(
        string $itemId,
        string $changeKey,
        string $version,
        string $url,
        string $username,
        string $password,
        bool $verifySsl,
        string $authType
    ): string {
        $soap = $this->buildGetItemRequest($itemId, $changeKey, $version);
        $response = $this->sendEwsRequest(
            $soap,
            'http://schemas.microsoft.com/exchange/services/2006/messages/GetItem',
            $url,
            $username,
            $password,
            $verifySsl,
            $authType
        );
        if (!$response->successful()) {
            return '';
        }
        $xmlString = $response->body();
        $cleanXml = preg_replace('/[^\x09\x0A\x0D\x20-\x7E\xC0-\xFF]/', '', $xmlString);
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        if (!$dom->loadXML($cleanXml, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_RECOVER)) {
            libxml_clear_errors();
            return '';
        }
        libxml_clear_errors();
        $xpath = new \DOMXPath($dom);
        $responseCode = $this->xpathValue($xpath, $dom, '//*[local-name()="ResponseCode"]');
        if ($responseCode && $responseCode !== 'NoError') {
            Log::warning('EWS GetItem hata kodu', [
                'code' => $responseCode,
                'item_id' => $itemId,
            ]);
            return '';
        }

        $nodes = $xpath->query('//*[local-name()="CalendarItem"]/*[local-name()="Body"]');
        if (!$nodes || $nodes->length === 0) {
            $nodes = $xpath->query('//*[local-name()="CalendarItem"]/*[local-name()="TextBody"]');
        }
        if (!$nodes || $nodes->length === 0) {
            Log::info('EWS GetItem body bos', [
                'item_id' => $itemId,
                'change_key' => $changeKey,
            ]);
            return '';
        }
        return $this->cleanText(trim($nodes->item(0)->textContent ?? ''));
    }

    private function parseFindItemResponse(string $xmlString): array
    {
        $length = strlen($xmlString);
        $head = substr($xmlString, 0, 500);
        $tail = substr($xmlString, max(0, $length - 500));

        // XML parse için daha toleranslı ol
        $cleanXml = preg_replace('/[^\x09\x0A\x0D\x20-\x7E\xC0-\xFF]/', '', $xmlString);
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $loaded = $dom->loadXML($cleanXml, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_RECOVER);
        if (!$loaded) {
            Log::error('EWS XML parse edilemedi. İlk 500 karakter:', [
                'length' => $length,
                'head' => $head,
                'tail' => $tail,
                'errors' => array_map(fn($e) => trim($e->message), libxml_get_errors()),
            ]);
            libxml_clear_errors();
            return ['error' => 'EWS cevabı parse edilemedi. Sunucu geçerli SOAP/XML dönmüyor olabilir.'];
        }
        libxml_clear_errors();

        // DOMXPath ile namespace bağımsız gez
        $xpath = new \DOMXPath($dom);
        $items = $xpath->query('//*[local-name()="CalendarItem"]') ?: [];

        $events = [];
        foreach ($items as $item) {
            $organizerName = $this->cleanText($this->xpathValue($xpath, $item, './/*[local-name()="Organizer"]//*[local-name()="Name"]'));
            $organizerEmail = $this->cleanText($this->xpathValue($xpath, $item, './/*[local-name()="Organizer"]//*[local-name()="EmailAddress"]'));
            $body = $this->xpathValue($xpath, $item, './*[local-name()="Body"]');
            $itemIdNode = $xpath->query('./*[local-name()="ItemId"]', $item);
            $itemId = '';
            $changeKey = '';
            if ($itemIdNode && $itemIdNode->length > 0) {
                $itemId = $itemIdNode->item(0)->attributes?->getNamedItem('Id')?->nodeValue ?? '';
                $changeKey = $itemIdNode->item(0)->attributes?->getNamedItem('ChangeKey')?->nodeValue ?? '';
            }
            $events[] = [
                'subject' => $this->xpathValue($xpath, $item, './*[local-name()="Subject"]'),
                'start' => $this->xpathValue($xpath, $item, './*[local-name()="Start"]'),
                'end' => $this->xpathValue($xpath, $item, './*[local-name()="End"]'),
                'location' => $this->xpathValue($xpath, $item, './*[local-name()="Location"]'),
                'organizer_name' => $organizerName,
                'organizer_email' => $organizerEmail,
                'body' => $body,
                'item_id' => $itemId,
                'change_key' => $changeKey,
            ];
        }

        return ['events' => $events];
    }

    private function xpathValue(\DOMXPath $xpath, \DOMNode $context, string $query): string
    {
        $nodes = $xpath->query($query, $context);
        if (!$nodes || $nodes->length === 0) {
            return '';
        }
        return trim($nodes->item(0)->textContent ?? '');
    }

    private function cleanText(string $text): string
    {
        // Yazdırılamayan karakterleri temizle
        $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }
        $text = preg_replace('/\x{FFFD}/u', '', $text) ?? $text;
        $text = preg_replace('/[^\P{C}\n\t\r]/u', '', $text) ?? $text;
        // Sonda kalan bozuk karakterleri temizle
        $text = preg_replace('/[^\p{L}\p{N}\s\.\-\(\)]+$/u', '', $text) ?? $text;
        return $text;
    }
}
