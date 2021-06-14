<?php

declare(strict_types = 1);

namespace InnStudio\SmsBao;

final class App
{
    private $configPath = '';

    private $accountId = '';

    private $accountPwd = '';

    private $phoneNumber = 0;

    private $sms = '';

    public function __construct(string $configPath)
    {
        $this->configPath = $configPath;
        $this->sms();
        $this->setPhoneNumber();
        $this->setConfig();

        if ($this->send()) {
            die(\json_encode([
                'code' => 0,
            ]));
        }

        die(\json_encode([
            'code' => 1,
        ]));
    }

    private function send(): bool
    {
        $query = \http_build_query([
            'u' => $this->accountId,
            'p' => \md5($this->accountPwd),
            'm' => $this->phoneNumber,
            'c' => $this->sms,
        ]);

        return '0' === \file_get_contents("https://api.smsbao.com/sms?{$query}");
    }

    private function sms(): void
    {
        $this->sms = \urldecode((string) \filter_input(\INPUT_GET, 'sms', \FILTER_SANITIZE_STRING));

        if ( ! $this->sms) {
            die('Invalid SMS content.');
        }
    }

    private function setPhoneNumber(): void
    {
        $this->phoneNumber = (int) \filter_input(\INPUT_GET, 'number', \FILTER_VALIDATE_INT);

        if ( ! $this->phoneNumber) {
            die('Invalid phone numbe');
        }
    }

    private function setConfig(): void
    {
        if ( ! \is_readable($this->configPath)) {
            die('Invalid config file path.');
        }

        $config = \json_decode((string) \file_get_contents($this->configPath), true);

        if ( ! \is_array($config)) {
            die('Invalid config file content.');
        }

        [
            'accountId'  => $this->accountId,
            'accountPwd' => $this->accountPwd,
        ] = $config;
    }
}
