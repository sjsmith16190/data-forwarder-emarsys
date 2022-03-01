<?php
namespace App\Http\Controllers;

use App\Exceptions\ConfigurationException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class EmarsysController extends Controller
{
    private $emarsysConfig = [];
    private $validationErrors = [];

    public function __construct()
    {
        $this->emarsysConfig = [
            'endpoint' => env('EMARSYS_ENDPOINT', null),
            'username' => env('EMARSYS_USERNAME', null),
            'secret' => env('EMARSYS_SECRET', null)
        ];

        $errorMsg = '';
        foreach ($this->emarsysConfig as $field => $value) {
            if (!$value) {
                $errorMsg += $field . ',';
            }
        }

        if (!empty($errorMsg)) {
            $errorMsg = 'Emarsys setup not correct. Check the following config fields: ' . $errorMsg;
            throw new ConfigurationException($errorMsg);
        }
    }

    /**
     * Router action to create an Emarsys contact from an incoming request
     *
     * @param $request Illuminate\Http\Request
     *
     * @return Illuminate\Http\Response
     */
    public function forward(Request $request, $emarsysApiRoute) {
        $actionUrl = $this->emarsysConfig['endpoint'] . "/${emarsysApiRoute}";

        if (!($wsseAuthHeaderString = $this->generateWsseAuthHeaderString())) {
            return $this->respond(
                ['success' => false, 'authentication-error' => $this->validationErrors],
                Response::HTTP_PROXY_AUTHENTICATION_REQUIRED,
                ['Proxy-Authenticate' => 'WSSE header generation failed. Please contact support.']
            );
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request->all()));
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["X-WSSE: ${wsseAuthHeaderString}", 'Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_URL, $actionUrl);

        $rawResponse = curl_exec($curl);
        curl_close($curl);

        return $this->respond($rawResponse);
    }

    /**
     * Function to authenticate our communciations with the Emarsys API with a custom X-WSSE header
     *
     * @return string
     */
    private function generateWsseAuthHeaderString() {
        $nonce = bin2hex(openssl_random_pseudo_bytes(16));
        $timestampStr = gmdate("c");
        $pwdDigest = base64_encode(sha1($nonce . $timestampStr . $this->emarsysConfig['secret'], false));

        $wsseHeaderString = 'UsernameToken Username="' . $this->emarsysConfig['username'] . '", '
            . "PasswordDigest=\"${pwdDigest}\", "
            . "Nonce=\"${nonce}\", "
            . "Created=\"${timestampStr}\"";

        return $wsseHeaderString;
    }
}
