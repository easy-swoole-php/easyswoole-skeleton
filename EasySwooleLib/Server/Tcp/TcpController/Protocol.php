<?php

namespace EasySwooleLib\Server\Tcp\TcpController;

use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\ServerManager;
use EasySwooleLib\Server\SubServerConfig;

class Protocol
{
    use Singleton;

    // Split package type
    public const EOF_SPLIT = 'EOF';

    public const LEN_SPLIT = 'LEN';

    /**
     * Default EOF chars
     */
    public const DEFAULT_EOF = "\r\n\r\n";

    /**
     * Use for pack data for length type
     */
    public const HEADER_PACK_FORMAT = 'N';

    /**
     * Use for unpack data for length type
     */
    public const HEADER_UNPACK_FORMAT = 'Nlen';

    /**
     * The default packer type name
     *
     * @var string
     */
    private $type = '';

    /**
     * @var int
     */
    private $packageMaxLength = 81920;

    // -------------- use package eof check --------------

    /**
     * Open package EOF check
     *
     * swoole.setting => [
     *  'package_max_length' => 81920,
     *  'open_eof_check'     => true,
     *  'open_eof_split'     => true,
     *  'package_eof'        => "\r\n\r\n",
     * ]
     *
     * @link https://wiki.swoole.com/wiki/page/285.html
     * @var bool
     */
    private $openEofCheck = true;

    /**
     * @var bool
     */
    private $openEofSplit = false;

    /**
     * @var string
     */
    private $packageEof = self::DEFAULT_EOF;

    // -------------- use package length check --------------

    /**
     * Open package length check
     *
     * swoole.setting => [
     *  'package_max_length'    => 81920,
     *  'open_length_check'     => true,
     *  'package_length_type'   => 'N',
     *  'package_length_offset' => 8,
     *  'package_body_offset'   => 16,
     * ]
     *          8-11 length
     *            |
     * [0===4===8===12===16|BODY...]
     *
     * @link https://wiki.swoole.com/wiki/page/287.html
     * @link https://github.com/matyhtf/framework/blob/3.0/src/core/Protocol/RPCServer.php
     * @var bool
     */
    private $openLengthCheck = false;

    /**
     * Header pack format
     *
     * @var string
     */
    private $headerPackFormat = self::HEADER_PACK_FORMAT;

    /**
     * Header unpack format
     *
     * @var string
     */
    private $headerUnpackFormat = self::HEADER_UNPACK_FORMAT;

    /**
     * @link https://wiki.swoole.com/wiki/page/463.html
     * @link https://www.php.net/manual/en/function.pack.php
     * @var string
     */
    private $packageLengthType = 'N';

    /**
     * The Nth byte is the value of the packet length
     *
     * @var int
     */
    private $packageLengthOffset = 8;

    /**
     * The first few bytes start to calculate the length
     *
     * @var int
     */
    private $packageBodyOffset = 16;

    /*********************************************************************
     * (Un)Packing data for server use
     ********************************************************************/

    /*********************************************************************
     * (Un)Packing data for client use
     ********************************************************************/

    /*********************************************************************
     * Simple pack methods(For quick test)
     ********************************************************************/

    /**
     * @param string $body
     *
     * @return string
     */
    public function packBody(string $body): string
    {
        // Use eof check
        if ($this->openEofCheck) {
            return $body . $this->packageEof;
        }

        // Use length check
        $format = $this->headerPackFormat;

        // TODO
        // Args sort please see self::HEADER_UNPACK_FORMAT
        return pack($format, strlen($body)) . $body;
    }

    /**
     * @param string $data
     *
     * @return array Return like: [head(null|array), body(string)]
     */
    public function unpackData(string $data): array
    {
        // Use eof check
        if ($this->openEofCheck) {
            return [
                ['type' => $this->type], // head
                rtrim($data, $this->packageEof), // body
            ];
        }

        // Use length check
        $format = $this->headerUnpackFormat;
        $headLen = $this->packageBodyOffset;

        // Like: ['type' => 'json', 'len' => 254, ]
        $headers = (array)unpack($format, substr($data, 0, $headLen));

        return [
            $headers,
            substr($data, $headLen), // body
        ];
    }

    /*********************************************************************
     * Getter/Setter methods
     ********************************************************************/

    /**
     * @return array
     */
    public function getConfig(): array
    {
        // Use EOF check
        if ($this->openEofCheck) {
            return [
                'open_eof_check'     => true,
                'open_eof_split'     => $this->openEofSplit,
                'package_eof'        => $this->packageEof,
                'package_max_length' => $this->packageMaxLength,
                'open_length_check'  => false,
            ];
        }

        // Use length check
        return [
            'open_length_check'     => true,
            'package_length_type'   => $this->packageLengthType,
            'package_length_offset' => $this->packageLengthOffset,
            'package_body_offset'   => $this->packageBodyOffset,
            'package_max_length'    => $this->packageMaxLength,
            'open_eof_check'        => false,
        ];
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        if ($type) {
            $this->type = $type;
        }
    }

    /**
     * @return string
     */
    public function getSplitType(): string
    {
        return $this->openEofCheck ? self::EOF_SPLIT : self::LEN_SPLIT;
    }

    /**
     * @return bool
     */
    public function isOpenLengthCheck(): bool
    {
        return $this->openLengthCheck;
    }

    /**
     * @param bool $openLengthCheck
     */
    public function setOpenLengthCheck($openLengthCheck): void
    {
        $this->openLengthCheck = (bool)$openLengthCheck;

        $this->openEofCheck = !$this->openLengthCheck;
    }

    /**
     * @return string
     */
    public function getPackageLengthType(): string
    {
        return $this->packageLengthType;
    }

    /**
     * @param string $packageLengthType
     */
    public function setPackageLengthType(string $packageLengthType): void
    {
        $this->packageLengthType = $packageLengthType;
    }

    /**
     * @return int
     */
    public function getPackageLengthOffset(): int
    {
        return $this->packageLengthOffset;
    }

    /**
     * @param int $packageLengthOffset
     */
    public function setPackageLengthOffset(int $packageLengthOffset): void
    {
        $this->packageLengthOffset = $packageLengthOffset;
    }

    /**
     * @return int
     */
    public function getPackageBodyOffset(): int
    {
        return $this->packageBodyOffset;
    }

    /**
     * @param int $packageBodyOffset
     */
    public function setPackageBodyOffset(int $packageBodyOffset): void
    {
        $this->packageBodyOffset = $packageBodyOffset;
    }

    /**
     * @return bool
     */
    public function isOpenEofCheck(): bool
    {
        return $this->openEofCheck;
    }

    /**
     * @param bool $openEofCheck
     */
    public function setOpenEofCheck($openEofCheck): void
    {
        $this->openEofCheck = (bool)$openEofCheck;

        $this->openLengthCheck = !$this->openEofCheck;
    }

    /**
     * @return bool
     */
    public function isOpenEofSplit(): bool
    {
        return $this->openEofSplit;
    }

    /**
     * @param bool $openEofSplit
     */
    public function setOpenEofSplit($openEofSplit): void
    {
        $this->openEofSplit = (bool)$openEofSplit;
    }

    /**
     * @return string
     */
    public function getPackageEof(): string
    {
        return $this->packageEof;
    }

    /**
     * @param string $packageEof
     */
    public function setPackageEof(string $packageEof): void
    {
        $this->packageEof = $packageEof;
    }

    /**
     * @return int
     */
    public function getPackageMaxLength(): int
    {
        return $this->packageMaxLength;
    }

    /**
     * @param int $packageMaxLength
     */
    public function setPackageMaxLength(int $packageMaxLength): void
    {
        $this->packageMaxLength = $packageMaxLength;
    }

    /**
     * @return string
     */
    public function getHeaderPackFormat(): string
    {
        return $this->headerPackFormat;
    }

    /**
     * @param string $headerPackFormat
     */
    public function setHeaderPackFormat(string $headerPackFormat): void
    {
        $this->headerPackFormat = $headerPackFormat;
    }

    /**
     * @return string
     */
    public function getHeaderUnpackFormat(): string
    {
        return $this->headerUnpackFormat;
    }

    /**
     * @param string $headerUnpackFormat
     */
    public function setHeaderUnpackFormat(string $headerUnpackFormat): void
    {
        $this->headerUnpackFormat = $headerUnpackFormat;
    }

    public function __construct(bool $isMainServer = true, string $subServerName = null)
    {
        $setting = [];

        if (!$isMainServer) {
            if ($subServerName) {
                $subServerRegister = SubServerConfig::getInstance()->getSubServerRegister($subServerName);
                $setting = $subServerRegister['setting'];
            }
        } else {
            $setting = config('MAIN_SERVER.SETTING');
        }

        $openEofCheck = $setting['open_eof_check'] ?? false;
        $this->setOpenEofCheck($openEofCheck);

        $openEofSpilt = $setting['open_eof_spilt'] ?? false;
        $this->setOpenEofSplit($openEofSpilt);

        $packageEof = $setting['package_eof'] ?? self::DEFAULT_EOF;
        $this->setPackageEof($packageEof);

        $packageMaxLength = $setting['package_max_length'] ?? 81920;
        $this->setPackageMaxLength($packageMaxLength);

        $openLengthCheck = $setting['open_length_check'] ?? false;
        $this->setOpenLengthCheck($openLengthCheck);

        $packageLengthType = $setting['package_length_type'] ?? $this->packageLengthType;
        $this->setPackageLengthType($packageLengthType);

        $packageLengthOffset = $setting['package_length_offset'] ?? $this->packageLengthOffset;
        $this->setPackageLengthOffset($packageLengthOffset);

        $packageBodyOffset = $setting['package_body_offset'] ?? $this->packageBodyOffset;
        $this->setPackageBodyOffset($packageBodyOffset);
    }
}
