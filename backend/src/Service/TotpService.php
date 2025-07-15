<?php

namespace App\Service;

class TotpService
{
    public function generateSecret(int $length = 16): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // base32
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    public function getOtpAuthUrl(string $email, string $issuer, string $secret): string
    {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            rawurlencode($issuer),
            $email,
            $secret,
            rawurlencode($issuer)
        );
    }

    public function getCode(string $secret, int $timeSlice = null): string
    {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }
        $secretKey = $this->base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $secretKey, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $truncatedHash = substr($hash, $offset, 4);
        $code = unpack('N', $truncatedHash)[1] & 0x7FFFFFFF;
        return str_pad($code % 1000000, 6, '0', STR_PAD_LEFT);
    }

    public function verifyCode(string $secret, string $code, int $window = 1): bool
    {
        $timeSlice = floor(time() / 30);
        for ($i = -$window; $i <= $window; $i++) {
            if ($this->getCode($secret, $timeSlice + $i) === $code) {
                return true;
            }
        }
        return false;
    }

    private function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper($secret);
        $secret = str_replace('=', '', $secret);
        $binaryString = '';
        for ($i = 0; $i < strlen($secret); $i += 8) {
            $x = '';
            for ($j = 0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@strpos($alphabet, @$secret[$i + $j]), 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            foreach ($eightBits as $char) {
                if (strlen($char) === 8) {
                    $binaryString .= chr(bindec($char));
                }
            }
        }
        return $binaryString;
    }
}