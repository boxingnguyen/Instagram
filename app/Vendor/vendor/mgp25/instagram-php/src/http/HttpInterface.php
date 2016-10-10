<?php

namespace InstagramAPI;

class HttpInterface
{
    protected $parent;
    protected $userAgent;
    protected $verifyPeer = false;
    protected $verifyHost = false;

    public function __construct($parent)
    {
        $this->parent = $parent;
        $this->userAgent = $this->parent->settings->get('user_agent');
    }

    public function request($endpoint, $post = null, $login = false)
    {
        if (!$this->parent->isLoggedIn && !$login) {
            throw new InstagramException("Not logged in\n");

            return;
        }

        $headers = [
        'Connection: close',
        'Accept: */*',
        'X-IG-Capabilities: 3Q4=',
        'X-IG-Connection-Type: WIFI',
        'Content-type: application/x-www-form-urlencoded; charset=UTF-8',
        'Accept-Language: en-US',
    ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, Constants::API_URL.$endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifyPeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifyHost);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');


        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        if ($this->parent->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->parent->proxyHost);
            if ($this->parent->proxyAuth) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->parent->proxyAuth);
            }
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $body = substr($resp, $header_len);

        if ($this->parent->debug) {
            if ($post) {
                if (php_sapi_name() == 'cli') {
                    $method = Utils::colouredString('POST:  ', 'light_blue');
                } else {
                    $method = 'POST:  ';
                }
                echo $method.$endpoint."\n";
            } else {
                if (php_sapi_name() == 'cli') {
                    $method = Utils::colouredString('GET:  ', 'light_blue');
                } else {
                    $method = 'GET:  ';
                }
                echo $method.$endpoint."\n";
            }
            if (!is_null($post)) {
                if (!is_array($post)) {
                    if (php_sapi_name() == 'cli') {
                        $dat = Utils::colouredString('DATA: ', 'yellow');
                    } else {
                        $dat = 'DATA: ';
                    }
                    echo $dat.urldecode($post)."\n";
                }
            }
            $bytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD));
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (php_sapi_name() == 'cli') {
                echo Utils::colouredString("← $httpCode \t $bytes", 'green')."\n";
            } else {
                echo "← $httpCode \t $bytes\n";
            }

            if ($this->parent->truncatedDebug && strlen($body) > 1000) {
                if (php_sapi_name() == 'cli') {
                    $res = Utils::colouredString('RESPONSE: ', 'cyan');
                } else {
                    $res = 'RESPONSE: ';
                }
                echo $res.substr($body, 0, 1000)."...\n\n";
            } else {
                if (php_sapi_name() == 'cli') {
                    $res = Utils::colouredString('RESPONSE: ', 'cyan');
                } else {
                    $res = 'RESPONSE: ';
                }
                echo $res.$body."\n\n";
            }
        }

        curl_close($ch);

        return [$header, json_decode($body, true)];
    }

    /**
     * @param $photo
     * @param null $caption
     * @param null $upload_id
     * @param null $customPreview
     * @param null $location
     * @param null $filter
     * @param bool $reel_flag
     *
     * @throws InstagramException
     */
    public function uploadPhoto($photo, $caption = null, $upload_id = null, $customPreview = null, $location = null, $filter = null, $reel_flag = false)
    {
        $endpoint = Constants::API_URL.'upload/photo/';
        $boundary = $this->parent->uuid;
        $helper = new AdaptImage();

        if (!is_null($upload_id) && is_null($customPreview)) {
            $fileToUpload = Utils::createVideoIcon($photo);
        } elseif (!is_null($customPreview)) {
            $fileToUpload = file_get_contents($customPreview);
        } else {
            $upload_id = number_format(round(microtime(true) * 1000), 0, '', '');
            $fileToUpload = $helper->checkAndProcess($photo);
        }

        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'upload_id',
                'data' => $upload_id,
            ],
            [
                'type' => 'form-data',
                'name' => '_uuid',
                'data' => $this->parent->uuid,
            ],
            [
                'type' => 'form-data',
                'name' => '_csrftoken',
                'data' => $this->parent->token,
            ],
            [
                'type' => 'form-data',
                'name' => 'image_compression',
                'data' => '{"lib_name":"jt","lib_version":"1.3.0","quality":"87"}',
            ],
            [
                'type'     => 'form-data',
                'name'     => 'photo',
                'data'     => $fileToUpload,
                'filename' => 'pending_media_'.number_format(round(microtime(true) * 1000), 0, '', '').'.jpg',
                'headers'  => [
                    'Content-Transfer-Encoding: binary',
                    'Content-type: application/octet-stream',
                ],
            ],
        ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
                'X-IG-Capabilities: 3Q4=',
                'X-IG-Connection-Type: WIFI',
                'Content-type: multipart/form-data; boundary='.$boundary,
                'Content-Length: '.strlen($data),
                'Accept-Language: en-US',
                'Accept-Encoding: gzip, deflate',
                'Connection: close',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifyPeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifyHost);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if ($this->parent->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->parent->proxyHost);
            if ($this->parent->proxyAuth) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->parent->proxyAuth);
            }
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = new UploadPhotoResponse(json_decode(substr($resp, $header_len), true));

        if ($this->parent->debug) {
            $endp = 'upload/photo/';
            if (php_sapi_name() == 'cli') {
                $method = Utils::colouredString('POST:  ', 'light_blue');
            } else {
                $method = 'POST:  ';
            }
            echo $method.$endp."\n";


            $uploadBytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_UPLOAD));
            if (php_sapi_name() == 'cli') {
                $dat = Utils::colouredString('→ '.$uploadBytes, 'yellow');
            } else {
                $dat = '→ '.$uploadBytes;
            }
            echo $dat."\n";

            $bytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD));
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (php_sapi_name() == 'cli') {
                echo Utils::colouredString("← $httpCode \t $bytes", 'green')."\n";
            } else {
                echo "← $httpCode \t $bytes\n";
            }

            if (php_sapi_name() == 'cli') {
                $res = Utils::colouredString('RESPONSE: ', 'cyan');
            } else {
                $res = 'RESPONSE: ';
            }
            echo $res.substr($resp, $header_len)."\n\n";
        }

        curl_close($ch);

        if (!$upload->isOk()) {
            throw new InstagramException($upload->getMessage());

            return;
        }

        if ($reel_flag) {
            $configure = $this->parent->configureToReel($upload->getUploadId(), $photo);
        } else {
            $configure = $this->parent->configure($upload->getUploadId(), $photo, $caption, $location, $filter);
        }

        if (!$configure->isOk()) {
            throw new InstagramException($configure->getMessage());
        }

        //$this->parent->expose();

        return $configure;
    }

    public function uploadVideo($video, $caption = null, $customPreview = null)
    {
        $videoData = file_get_contents($video);

        $endpoint = Constants::API_URL.'upload/video/';
        $boundary = $this->parent->uuid;
        $upload_id = round(microtime(true) * 1000);
        $bodies = [
          [
              'type' => 'form-data',
              'name' => 'upload_id',
              'data' => $upload_id,
          ],
          [
              'type' => 'form-data',
              'name' => '_csrftoken',
              'data' => $this->parent->token,
          ],
          [
              'type'   => 'form-data',
              'name'   => 'media_type',
              'data'   => '2',
          ],
          [
              'type' => 'form-data',
              'name' => '_uuid',
              'data' => $this->parent->uuid,
          ],
      ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
          'Connection: keep-alive',
          'Accept: */*',
          'Host: i.instagram.com',
          'Content-type: multipart/form-data; boundary='.$boundary,
          'Accept-Language: en-en',
      ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if ($this->parent->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->parent->proxyHost);
            if ($this->parent->proxyAuth) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->parent->proxyAuth);
            }
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $body = new UploadJobVideoResponse(json_decode(substr($resp, $header_len), true));

        $uploadUrl = $body->getVideoUploadUrl();
        $job = $body->getVideoUploadJob();

        $request_size = floor(strlen($videoData) / 4);
        $lastRequestExtra = (strlen($videoData) - ($request_size * 4));

        if ($this->parent->debug) {
            $endp = 'upload/video/';
            if (php_sapi_name() == 'cli') {
                $method = Utils::colouredString('POST:  ', 'light_blue');
            } else {
                $method = 'POST:  ';
            }
            echo $method.$endp."\n";


            $uploadBytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_UPLOAD));
            if (php_sapi_name() == 'cli') {
                $dat = Utils::colouredString('→ '.$uploadBytes, 'yellow');
            } else {
                $dat = '→ '.$uploadBytes;
            }
            echo $dat."\n";

            $bytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD));
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (php_sapi_name() == 'cli') {
                echo Utils::colouredString("← $httpCode \t $bytes", 'green')."\n";
            } else {
                echo "← $httpCode \t $bytes\n";
            }

            if (php_sapi_name() == 'cli') {
                $res = Utils::colouredString('RESPONSE: ', 'cyan');
            } else {
                $res = 'RESPONSE: ';
            }
            echo $res.substr($resp, $header_len)."\n\n";
        }

        for ($a = 0; $a <= 3; $a++) {
            $start = ($a * $request_size);
            $end = ($a + 1) * $request_size + ($a == 3 ? $lastRequestExtra : 0);

            $headers = [
              'Connection: keep-alive',
              'Accept: */*',
              'Host: upload.instagram.com',
              'Cookie2: $Version=1',
              'Accept-Encoding: gzip, deflate',
              'Content-Type: application/octet-stream',
              'Session-ID: '.$upload_id,
              'Accept-Language: en-en',
              'Content-Disposition: attachment; filename="video.mov"',
              'Content-Length: '.($end - $start),
              'Content-Range: '.'bytes '.$start.'-'.($end - 1).'/'.strlen($videoData),
              'job: '.$job,
          ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $uploadUrl);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_VERBOSE, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, substr($videoData, $start, $end));

            if ($this->parent->proxy) {
                curl_setopt($ch, CURLOPT_PROXY, $this->parent->proxyHost);
                if ($this->parent->proxyAuth) {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->parent->proxyAuth);
                }
            }

            $result = curl_exec($ch);
            $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($result, $header_len);
            $array[] = [$body];

            //here another debug when improved echo debugging
        }
        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = new UploadVideoResponse(json_decode(substr($resp, $header_len), true));

        if ($this->parent->debug) {
            $endp = 'upload/photo/';
            if (php_sapi_name() == 'cli') {
                $method = Utils::colouredString('POST:  ', 'light_blue');
            } else {
                $method = 'POST:  ';
            }
            echo $method.$endp."\n";


            $uploadBytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_UPLOAD));
            if (php_sapi_name() == 'cli') {
                $dat = Utils::colouredString('→ '.$uploadBytes, 'yellow');
            } else {
                $dat = '→ '.$uploadBytes;
            }
            echo $dat."\n";

            $bytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD));
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (php_sapi_name() == 'cli') {
                echo Utils::colouredString("← $httpCode \t $bytes", 'green')."\n";
            } else {
                echo "← $httpCode \t $bytes\n";
            }

            if (php_sapi_name() == 'cli') {
                $res = Utils::colouredString('RESPONSE: ', 'cyan');
            } else {
                $res = 'RESPONSE: ';
            }
            echo $res.substr($resp, $header_len)."\n\n";
        }

        curl_close($ch);

        $configure = $this->parent->configureVideo($upload_id, $video, $caption, $customPreview);
        //$this->parent->expose();

        return $configure;
    }

    public function changeProfilePicture($photo)
    {
        if (is_null($photo)) {
            echo "Photo not valid\n\n";

            return;
        }

        $uData = json_encode([
        '_csrftoken' => $this->parent->token,
        '_uuid'      => $this->parent->uuid,
        '_uid'       => $this->parent->username_id,
      ]);

        $endpoint = Constants::API_URL.'accounts/change_profile_picture/';
        $boundary = $this->parent->uuid;
        $bodies = [
        [
          'type' => 'form-data',
          'name' => 'ig_sig_key_version',
          'data' => Constants::SIG_KEY_VERSION,
        ],
        [
          'type' => 'form-data',
          'name' => 'signed_body',
          'data' => hash_hmac('sha256', $uData, Constants::IG_SIG_KEY).$uData,
        ],
        [
          'type'     => 'form-data',
          'name'     => 'profile_pic',
          'data'     => file_get_contents($photo),
          'filename' => 'profile_pic',
          'headers'  => [
            'Content-type: application/octet-stream',
            'Content-Transfer-Encoding: binary',
          ],
        ],
      ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
          'Proxy-Connection: keep-alive',
          'Connection: keep-alive',
          'Accept: */*',
          'Content-type: multipart/form-data; boundary='.$boundary,
          'Accept-Language: en-en',
      ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifyPeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifyHost);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if ($this->parent->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->parent->proxyHost);
            if ($this->parent->proxyAuth) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->parent->proxyAuth);
            }
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = json_decode(substr($resp, $header_len), true);

        if ($this->parent->debug) {
            $endp = 'accounts/change_profile_picture/';
            if (php_sapi_name() == 'cli') {
                $method = Utils::colouredString('POST:  ', 'light_blue');
            } else {
                $method = 'POST:  ';
            }
            echo $method.$endp."\n";


            $uploadBytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_UPLOAD));
            if (php_sapi_name() == 'cli') {
                $dat = Utils::colouredString('→ '.$uploadBytes, 'yellow');
            } else {
                $dat = '→ '.$uploadBytes;
            }
            echo $dat."\n";

            $bytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD));
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (php_sapi_name() == 'cli') {
                echo Utils::colouredString("← $httpCode \t $bytes", 'green')."\n";
            } else {
                echo "← $httpCode \t $bytes\n";
            }

            if (php_sapi_name() == 'cli') {
                $res = Utils::colouredString('RESPONSE: ', 'cyan');
            } else {
                $res = 'RESPONSE: ';
            }
            echo $res.substr($resp, $header_len)."\n\n";
        }

        curl_close($ch);
    }

    public function direct_share($media_id, $recipients, $text = null)
    {
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        $string = [];
        foreach ($recipients as $recipient) {
            $string[] = "\"$recipient\"";
        }

        $recipient_users = implode(',', $string);

        $endpoint = Constants::API_URL.'direct_v2/threads/broadcast/media_share/?media_type=photo';
        $boundary = $this->parent->uuid;
        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'media_id',
                'data' => $media_id,
            ],
            [
                'type' => 'form-data',
                'name' => 'recipient_users',
                'data' => "[[$recipient_users]]",
            ],
            [
                'type' => 'form-data',
                'name' => 'client_context',
                'data' => $this->parent->uuid,
            ],
            [
                'type' => 'form-data',
                'name' => 'thread_ids',
                'data' => '["0"]',
            ],
            [
                'type' => 'form-data',
                'name' => 'text',
                'data' => is_null($text) ? '' : $text,
            ],
        ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
                'Proxy-Connection: keep-alive',
                'Connection: keep-alive',
                'Accept: */*',
                'Content-type: multipart/form-data; boundary='.$boundary,
                'Accept-Language: en-en',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->parent->debug);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifyPeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifyHost);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if ($this->parent->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->parent->proxyHost);
            if ($this->parent->proxyAuth) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->parent->proxyAuth);
            }
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = json_decode(substr($resp, $header_len), true);

        curl_close($ch);
    }

    public function direct_message($recipients, $text)
    {
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        $string = [];
        foreach ($recipients as $recipient) {
            $string[] = "\"$recipient\"";
        }

        $recipient_users = implode(',', $string);

        $endpoint = Constants::API_URL.'direct_v2/threads/broadcast/text/';
        $boundary = $this->parent->uuid;
        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'recipient_users',
                'data' => "[[$recipient_users]]",
            ],
            [
                'type' => 'form-data',
                'name' => 'client_context',
                'data' => $this->parent->uuid,
            ],
            [
                'type' => 'form-data',
                'name' => 'thread_ids',
                'data' => '["0"]',
            ],
            [
                'type' => 'form-data',
                'name' => 'text',
                'data' => is_null($text) ? '' : $text,
            ],
        ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
                'Proxy-Connection: keep-alive',
                'Connection: keep-alive',
                'Accept: */*',
                'Content-type: multipart/form-data; boundary='.$boundary,
                'Accept-Language: en-en',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->parent->debug);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifyPeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifyHost);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->IGDataPath.$this->parent->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if ($this->parent->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->parent->proxyHost);
            if ($this->parent->proxyAuth) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->parent->proxyAuth);
            }
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = json_decode(substr($resp, $header_len), true);

        curl_close($ch);
    }

    protected function buildBody($bodies, $boundary)
    {
        $body = '';
        foreach ($bodies as $b) {
            $body .= '--'.$boundary."\r\n";
            $body .= 'Content-Disposition: '.$b['type'].'; name="'.$b['name'].'"';
            if (isset($b['filename'])) {
                $ext = pathinfo($b['filename'], PATHINFO_EXTENSION);
                $body .= '; filename="'.'pending_media_'.number_format(round(microtime(true) * 1000), 0, '', '').'.'.$ext.'"';
            }
            if (isset($b['headers']) && is_array($b['headers'])) {
                foreach ($b['headers'] as $header) {
                    $body .= "\r\n".$header;
                }
            }

            $body .= "\r\n\r\n".$b['data']."\r\n";
        }
        $body .= '--'.$boundary.'--';

        return $body;
    }

    public function verifyPeer($enable)
    {
        $this->verifyPeer = $enable;
    }

    public function verifyHost($enable)
    {
        $this->verifyHost = $enable;
    }
}
