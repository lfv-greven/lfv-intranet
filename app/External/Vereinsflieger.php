<?php

namespace App\External;

use App\Enums\VereinsfliegerPriority;
use App\Exceptions\VereinsfliegerDeferred;
use App\Exceptions\VereinsfliegerTransportException;
use App\Services\VereinsfliegerRequestGate;

class Vereinsflieger
{
    private $InterfaceUrl = 'https://vereinsflieger.de/interface/rest/';

    // Flightcenter-Kunden müssen hier folgende URL nehmen 'https://www.flightcenterplus.de/interface/rest/'
    // VereinsfliegerRestInterface->SetInterfaceUrl('https://www.flightcenterplus.de/interface/rest/');
    private ?string $AccessToken;

    private $HttpStatusCode = 0;

    private $aResponse = [];

    private array $ResponseHeaders = [];

    public function __construct(
        private readonly VereinsfliegerRequestGate $requestGate,
        private readonly VereinsfliegerPriority $priority,
        ?string $accessToken = null,
    ) {
        $this->AccessToken = $accessToken;
    }

    public function GetAccessToken()
    {
        return $this->AccessToken;
    }

    // =============================================================================================
    // Anmelden
    // =============================================================================================
    public function SignIn($UserName, $Password, $AuthSecret = '')
    {
        // Accesstoken holen
        $this->SendRequest('GET', 'auth/accesstoken', null);
        if ($this->HttpStatusCode != 200 || ! $this->aResponse) {
            return false;
        }
        $this->AccessToken = $this->aResponse['accesstoken'];
        $Password = mb_convert_encoding($Password, 'ISO-8859-1', 'UTF-8');
        $PassWordHash = md5($Password);
        // Anmelden
        $Data = [
            'accesstoken' => $this->AccessToken,
            'username' => $UserName,
            'password' => $PassWordHash,
            'cid' => config('services.vereinsflieger.cid'),
            'appkey' => config('services.vereinsflieger.key'),
            'auth_secret' => $AuthSecret,
        ];
        $this->SendRequest('POST', 'auth/signin', $Data);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // Abmelden
    // =============================================================================================
    public function SignOut()
    {
        $Data = ['accesstoken' => $this->AccessToken];
        $this->SendRequest('DELETE', 'auth/signout/'.$this->AccessToken, $Data);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetUser
    // =============================================================================================
    public function GetUser()
    {
        $Data = ['accesstoken' => $this->AccessToken];
        $this->SendRequest('POST', 'auth/getuser', $Data);
        if ($this->HttpStatusCode == 200) {
            return $this->aResponse;
        }

        return [];
    }

    // =============================================================================================
    // Login from iframe
    // =============================================================================================
    public function IframeLogin(string $accessToken)
    {
        $Data = ['accesstoken' => $accessToken];
        $this->SendRequest('POST', 'auth/getuser', $Data);
        if ($this->HttpStatusCode == 200) {
            return $this->aResponse;
        }

        return null;
    }

    // =============================================================================================
    // InsertFlight
    // =============================================================================================
    public function InsertFlight($aFlighData)
    {
        $aFlighData['accesstoken'] = $this->AccessToken;
        $this->SendRequest('POST', 'flight/add', $aFlighData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // UpdateFlight
    // =============================================================================================
    public function UpdateFlight($Flid, $aFlighData)
    {
        $aFlighData['accesstoken'] = $this->AccessToken;
        $this->SendRequest('PUT', 'flight/edit/'.intval($Flid), $aFlighData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // JoinTowFlights
    // =============================================================================================
    public function JoinTowFlights($Flid, $FlidTow)
    {
        $aData = [];
        $aData['accesstoken'] = $this->AccessToken;
        $aData['flid'] = $Flid;
        $aData['flidtow'] = $FlidTow;
        $this->SendRequest('PUT', 'flight/jointowflights', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // DeleteFlight
    // =============================================================================================
    public function DeleteFlight($Flid)
    {
        $aFlighData['accesstoken'] = $this->AccessToken;
        $this->SendRequest('DELETE', 'flight/delete/'.intval($Flid), $aFlighData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetFlight
    // =============================================================================================
    public function GetFlight($Flid)
    {
        $aFlighData['accesstoken'] = $this->AccessToken;
        $this->SendRequest('POST', 'flight/get/'.intval($Flid), $aFlighData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetFlights_Today
    // =============================================================================================
    public function GetFlights_today()
    {
        $aData = ['accesstoken' => $this->AccessToken];
        $this->SendRequest('POST', 'flight/list/today', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetFlights_Date
    // =============================================================================================
    public function GetFlights_date($Date)
    {
        $aData = [
            'accesstoken' => $this->AccessToken,
            'dateparam' => $Date,
        ];
        $this->SendRequest('POST', 'flight/list/date', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetFlights_Daterange
    // =============================================================================================
    public function GetFlights_Daterange($DateFrom, $DateTo)
    {
        $aData = [
            'accesstoken' => $this->AccessToken,
            'datefrom' => $DateFrom,
            'dateto' => $DateTo,
        ];
        $this->SendRequest('POST', 'flight/list/daterange', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetFlights_Modified
    // =============================================================================================
    public function GetFlights_Modified($Days)
    {
        $aData = [
            'accesstoken' => $this->AccessToken,
            'days' => $Days,
        ];
        $this->SendRequest('POST', 'flight/list/modified', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetFlights_NoDate
    // =============================================================================================
    public function GetFlights_nodate()
    {
        $aData = [
            'accesstoken' => $this->AccessToken];
        $this->SendRequest('POST', 'flight/list/nodate', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetFlights_plane
    // =============================================================================================
    public function GetFlights_plane($Callsign, $Count)
    {
        $aData = [
            'accesstoken' => $this->AccessToken,
            'callsign' => $Callsign,
            'count' => $Count];
        $this->SendRequest('POST', 'flight/list/plane', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetFlights_user
    // =============================================================================================
    public function GetFlights_user($Uid, $Count)
    {
        $aData = [
            'accesstoken' => $this->AccessToken,
            'uid' => $Uid,
            'count' => $Count];
        $this->SendRequest('POST', 'flight/list/user', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetFlights_myflights
    // =============================================================================================
    public function GetFlights_myflights($Count)
    {
        $aData = [
            'accesstoken' => $this->AccessToken,
            'count' => $Count];
        $this->SendRequest('POST', 'flight/list/myflights', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetPublicCalendar
    // =============================================================================================
    public function GetPublicCalendar($HpAccessCode = '')
    {
        $aData = [
            'hpaccesscode' => $HpAccessCode,
        ];
        $this->SendRequest('POST', 'calendar/list/public', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetMyCalendar
    // =============================================================================================
    public function GetMyCalendar()
    {
        $aData = [
            'accesstoken' => $this->AccessToken];
        $this->SendRequest('GET', 'calendar/list/mycalendar', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetUsers
    // =============================================================================================
    public function GetUsers()
    {
        $aData = ['accesstoken' => $this->AccessToken];
        $this->SendRequest('POST', 'user/list', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetReservations
    // =============================================================================================
    public function GetReservations()
    {
        $aData = [
            'accesstoken' => $this->AccessToken,
        ];
        $this->SendRequest('POST', 'reservation/list/active', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetAirplaneMaintenanceData
    // =============================================================================================
    public function GetAirplaneMaintenanceData($Callsign)
    {
        $aData = [
            'accesstoken' => $this->AccessToken,
        ];
        $this->SendRequest('POST', 'maintenance/airplane/'.$Callsign, $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // InsertAccountTransaction
    // =============================================================================================
    public function InsertAccountTransaction($aData)
    {
        $aData['accesstoken'] = $this->AccessToken;
        $this->SendRequest('POST', 'account/add', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // UpdateAccountTransaction
    // =============================================================================================
    public function UpdateAccountTransaction($Adid, $aData)
    {
        $aData['accesstoken'] = $this->AccessToken;
        $this->SendRequest('PUT', 'account/edit/'.intval($Adid), $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetAccountTransaction
    // =============================================================================================
    public function GetAccountTransaction($Adid)
    {
        $aData = [
            'accesstoken' => $this->AccessToken,
        ];
        $this->SendRequest('POST', 'account/get/'.intval($Adid), $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetAccountTransactions_today
    // =============================================================================================
    public function GetAccountTransactions_today()
    {
        $aData = [
            'accesstoken' => $this->AccessToken,
        ];
        $this->SendRequest('POST', 'account/list/today', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetAccountTransactions_year
    // =============================================================================================
    public function GetAccountTransactions_year($Year)
    {
        $aData = [
            'accesstoken' => $this->AccessToken,
            'year' => $Year,
        ];
        $this->SendRequest('POST', 'account/list/year', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetAccountTransactions_daterange
    // =============================================================================================
    public function GetAccountTransactions_daterange($DateFrom, $DateTo)
    {
        $aData = [
            'accesstoken' => $this->AccessToken,
            'datefrom' => $DateFrom,
            'dateto' => $DateTo,
        ];
        $this->SendRequest('POST', 'account/list/daterange', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetWorkHours_daterange
    // =============================================================================================
    public function GetWorkHours_daterange($DateFrom, $DateTo)
    {
        $aData = [
            'accesstoken' => $this->AccessToken,
            'datefrom' => $DateFrom,
            'dateto' => $DateTo,
        ];
        $this->SendRequest('POST', 'workhours/list/daterange', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetWorkHourCategories
    // =============================================================================================
    public function GetWorkHourCategories()
    {
        $aData = ['accesstoken' => $this->AccessToken];
        $this->SendRequest('POST', 'workhourcategories/list', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // InsertWorkHours
    // =============================================================================================
    public function InsertWorkHours($aData)
    {
        $aData['accesstoken'] = $this->AccessToken;
        $this->SendRequest('POST', 'workhours/add', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetArticles
    // =============================================================================================
    public function GetArticles()
    {
        $aData = ['accesstoken' => $this->AccessToken];
        $this->SendRequest('POST', 'articles/list', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetSales_today
    // =============================================================================================
    public function GetSales_today()
    {
        $aData = ['accesstoken' => $this->AccessToken];
        $this->SendRequest('POST', 'sale/list/today', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetSales_Date
    // =============================================================================================
    public function GetSales_date($Date)
    {
        $aData = [
            'accesstoken' => $this->AccessToken,
            'dateparam' => $Date,
        ];
        $this->SendRequest('POST', 'sale/list/date', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetSales_Daterange
    // =============================================================================================
    public function GetSales_Daterange($DateFrom, $DateTo)
    {
        $aData = [
            'accesstoken' => $this->AccessToken,
            'datefrom' => $DateFrom,
            'dateto' => $DateTo,
        ];
        $this->SendRequest('POST', 'sale/list/daterange', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetSales_Modified
    // =============================================================================================
    public function GetSales_Modified($Days)
    {
        $aData = [
            'accesstoken' => $this->AccessToken,
            'days' => $Days,
        ];
        $this->SendRequest('POST', 'sale/list/modified', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // InsertSale
    // =============================================================================================
    public function InsertSale($aData)
    {
        $aData['accesstoken'] = $this->AccessToken;
        $this->SendRequest('POST', 'sale/add', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetBackup
    // =============================================================================================
    public function GetBackup()
    {
        $aData = ['accesstoken' => $this->AccessToken];
        $this->SendRequest('GET', 'backup/getzip', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetVouchers
    // =============================================================================================
    public function GetVouchers()
    {
        $aData = ['accesstoken' => $this->AccessToken];
        $this->SendRequest('POST', 'voucher/list', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // InsertVoucher
    // =============================================================================================
    public function InsertVoucher($aData)
    {
        $aData['accesstoken'] = $this->AccessToken;
        $this->SendRequest('POST', 'voucher/add', $aData);

        return $this->HttpStatusCode == 200;
    }

    // =============================================================================================
    // GetHttpStatusCode
    // =============================================================================================
    public function GetHttpStatusCode()
    {
        return $this->HttpStatusCode;
    }

    // =============================================================================================
    // GetResponse
    // =============================================================================================
    public function GetResponse()
    {
        return $this->aResponse;
    }

    public function GetRetryAfter(): ?int
    {
        $retryAfter = $this->ResponseHeaders['retry-after'][0] ?? null;

        if (! is_string($retryAfter) || $retryAfter === '') {
            return null;
        }

        if (ctype_digit($retryAfter)) {
            return max(1, (int) $retryAfter);
        }

        $timestamp = strtotime($retryAfter);

        return $timestamp === false ? null : max(1, $timestamp - time());
    }

    // =============================================================================================
    // SendRequest
    // =============================================================================================
    private function SendRequest($Method, $Resource, $Data)
    {
        $this->ResponseHeaders = [];
        $this->requestGate->admit($this->priority, $this->isUnauthenticated($Resource));

        $InterfaceUrl = $this->InterfaceUrl.$Resource;
        $CurlHandle = curl_init();

        if ($CurlHandle === false) {
            throw new VereinsfliegerTransportException($Resource, 'Unable to initialize cURL.');
        }

        curl_setopt($CurlHandle, CURLOPT_URL, $InterfaceUrl);
        switch ($Method) {
            case 'GET':
            case 'POST':
                $Fields = http_build_query(is_array($Data) ? $Data : [], '', '&');
                curl_setopt($CurlHandle, CURLOPT_HTTPHEADER, ['Content-Length: '.strlen($Fields)]);
                curl_setopt($CurlHandle, CURLOPT_POST, 1);
                curl_setopt($CurlHandle, CURLOPT_POSTFIELDS, $Fields);
                break;
            case 'PUT':
                // $Fields = http_build_query(is_array($Data) ? $Data : array());
                $Fields = http_build_query(is_array($Data) ? $Data : [], '', '&');
                curl_setopt($CurlHandle, CURLOPT_HTTPHEADER, ['Content-Length: '.strlen($Fields)]);
                curl_setopt($CurlHandle, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($CurlHandle, CURLOPT_POSTFIELDS, $Fields);
                break;
            case 'DELETE':
                // $Fields = http_build_query(is_array($Data) ? $Data : array());
                $Fields = http_build_query(is_array($Data) ? $Data : [], '', '&');
                curl_setopt($CurlHandle, CURLOPT_HTTPHEADER, ['Content-Length: '.strlen($Fields)]);
                curl_setopt($CurlHandle, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($CurlHandle, CURLOPT_POSTFIELDS, $Fields);
                break;
        }
        curl_setopt($CurlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($CurlHandle, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($CurlHandle, CURLOPT_CONNECTTIMEOUT, (int) config('services.vereinsflieger.http.connect_timeout_seconds', 5));
        curl_setopt($CurlHandle, CURLOPT_TIMEOUT, (int) config('services.vereinsflieger.http.timeout_seconds', 20));
        curl_setopt($CurlHandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($CurlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($CurlHandle, CURLOPT_HEADERFUNCTION, function ($handle, string $header): int {
            $length = strlen($header);
            $parts = explode(':', $header, 2);

            if (count($parts) === 2) {
                $name = strtolower(trim($parts[0]));
                $value = trim($parts[1]);

                if ($name !== '') {
                    $this->ResponseHeaders[$name][] = $value;
                }
            }

            return $length;
        });
        $Html = curl_exec($CurlHandle);

        $this->HttpStatusCode = curl_getinfo($CurlHandle, CURLINFO_HTTP_CODE);
        if ($Html === false) {
            $error = curl_error($CurlHandle);
            curl_close($CurlHandle);

            throw new VereinsfliegerTransportException($Resource, $error === '' ? 'Unknown cURL error.' : $error);
        }

        if ($this->HttpStatusCode === 429) {
            $retryAfter = $this->requestGate->cooldown($this->GetRetryAfter());
            curl_close($CurlHandle);

            throw new VereinsfliegerDeferred($retryAfter, 'upstream_429');
        }

        $ContentType = curl_getinfo($CurlHandle, CURLINFO_CONTENT_TYPE);
        switch ($ContentType) {
            case 'application/zip':
                $this->aResponse = $Html;
                curl_close($CurlHandle);

                return true;
        }
        curl_close($CurlHandle);
        $this->aResponse = json_decode($Html, true);
        if (! $this->aResponse) {
            return false;
        }

        return true;
    }

    private function isUnauthenticated(string $resource): bool
    {
        return $this->AccessToken === null
            || in_array($resource, ['auth/accesstoken', 'auth/signin'], true);
    }

    // =============================================================================================
    // SetInterfaceUrl
    // =============================================================================================
    public function SetInterfaceUrl($InterfaceUrl)
    {
        return $this->InterfaceUrl = $InterfaceUrl;
    }
}
