<?php

namespace Http\Psr7Test;

use F3\Http\Factory\Psr17Factory as FatfreeFactory;
use GuzzleHttp\Psr7\Stream as GuzzleStream;
use GuzzleHttp\Psr7\UploadedFile as GuzzleUploadedFile;
use GuzzleHttp\Psr7\Uri as GuzzleUri;
use HttpSoft\Message\StreamFactory as HttpSoftStreamFactory;
use HttpSoft\Message\UploadedFile as HttpSoftUploadedFile;
use HttpSoft\Message\Uri as HttpSoftUri;
use Laminas\Diactoros\StreamFactory as LaminasStreamFactory;
use Laminas\Diactoros\Uri as LaminasUri;
use Laminas\Diactoros\UploadedFile as LaminasUploadedFile;
use Nyholm\Psr7\Factory\Psr17Factory as NyholmFactory;
use PHPUnit\Framework\TestCase;
use RingCentral\Psr7\Uri as RingCentralUri;
use function RingCentral\Psr7\stream_for as ring_central_stream_for;
use GuzzleHttp\Psr7\Utils as GuzzleUtils;
use Http\Message\StreamFactory as HttplugStreamFactory;
use Http\Message\UriFactory as HttplugUriFactory;
use Psr\Http\Message\StreamFactoryInterface as PsrStreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface as PsrUploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface as PsrUriFactoryInterface;
use Psr\Http\Message\UriInterface as PsrUriInterface;
use Slim\Psr7\Uri as SlimUri;
use Slim\Psr7\Factory\UriFactory as SlimUriFactory;
use Slim\Psr7\Factory\StreamFactory as SlimStreamFactory;
use Slim\Psr7\Factory\UploadedFileFactory as SlimUploadedFileFactory;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class BaseTest extends TestCase
{
    protected function assertNotSameObject($a, $b)
    {
        $this->assertFalse($a === $b, 'Object does not have different references.');
    }

    protected function buildUri($uri)
    {
        if (defined('URI_FACTORY')) {
            $factoryClass = URI_FACTORY;
            $factory = new $factoryClass();
            if ($factory instanceof HttplugUriFactory) {
                return $factory->createUri($uri);
            }
            if ($factory instanceof PsrUriFactoryInterface) {
                if ($uri instanceof PsrUriInterface) {
                    return $uri;
                }

                return $factory->createUri($uri);
            }

            throw new \RuntimeException('Constant "URI_FACTORY" must be a reference to a '.HttplugUriFactory::class.' or '.PsrUriFactoryInterface::class);
        }

        if (class_exists(HttpSoftUri::class)) {
            return new HttpSoftUri($uri);
        }

        if (class_exists(GuzzleUri::class)) {
            return new GuzzleUri($uri);
        }

        if (class_exists(SlimUri::class)) {
            if (class_exists(SlimUriFactory::class)) {
                return (new SlimUriFactory())->createUri($uri);
            }

            return SlimUri::createFromString($uri);
        }

        if (class_exists(LaminasUri::class)) {
            return new LaminasUri($uri);
        }

        if (class_exists(NyholmFactory::class)) {
            return (new NyholmFactory())->createUri($uri);
        }

        if (class_exists(RingCentralUri::class)) {
            return new RingCentralUri($uri);
        }

        if (class_exists(FatfreeFactory::class)) {
            return (new FatfreeFactory())->createUri($uri);
        }

        throw new \RuntimeException('Could not create URI. Check your config');
    }

    protected function buildStream($data)
    {
        if (defined('STREAM_FACTORY')) {
            $factoryClass = STREAM_FACTORY;
            $factory = new $factoryClass();
            if ($factory instanceof HttplugStreamFactory) {
                return $factory->createStream($data);
            }
            if ($factory instanceof PsrStreamFactoryInterface) {
                if (is_string($data)) {
                    return $factory->createStream($data);
                }

                return $factory->createStreamFromResource($data);
            }

            throw new \RuntimeException('Constant "STREAM_FACTORY" must be a reference to a '.HttplugStreamFactory::class.' or '.PsrStreamFactoryInterface::class);
        }

        if (class_exists(GuzzleStream::class)) {
            return GuzzleUtils::streamFor($data);
        }

        $factory = null;
        if (class_exists(HttpSoftStreamFactory::class)) {
            $factory = new HttpSoftStreamFactory();
        }
        if (class_exists(LaminasStreamFactory::class)) {
            $factory = new LaminasStreamFactory();
        }
        if (class_exists(NyholmFactory::class)) {
            $factory = new NyholmFactory();
        }
        if (class_exists(SlimStreamFactory::class)) {
            $factory = new SlimStreamFactory();
        }
        if (class_exists(FatfreeFactory::class)) {
            $factory = new FatfreeFactory();
        }
        if ($factory) {
            if (is_string($data)) {
                return $factory->createStream($data);
            }

            return $factory->createStreamFromResource($data);
        }

        if (function_exists('ring_central_stream_for')) {
            return ring_central_stream_for($data);
        }

        throw new \RuntimeException('Could not create Stream. Check your config');
    }

    protected function buildUploadableFile($data)
    {
        if (defined('UPLOADED_FILE_FACTORY')) {
            $factoryClass = UPLOADED_FILE_FACTORY;
            $factory = new $factoryClass();
            if (!$factory instanceof PsrUploadedFileFactoryInterface) {
                throw new \RuntimeException('Constant "UPLOADED_FILE_FACTORY" must be a reference to a '.PsrUploadedFileFactoryInterface::class);
            }

            $stream = $this->buildStream($data);

            return $factory->createUploadedFile($stream);
        }

        if (class_exists(HttpSoftUploadedFile::class)) {
            return new HttpSoftUploadedFile($data, strlen($data), 0);
        }

        if (class_exists(GuzzleUploadedFile::class)) {
            return new GuzzleUploadedFile($data, strlen($data), 0);
        }

        if (class_exists(LaminasUploadedFile::class)) {
            return new LaminasUploadedFile($data, strlen($data), 0);
        }

        if (class_exists(NyholmFactory::class)) {
            $stream = $this->buildStream($data);

            return (new NyholmFactory())->createUploadedFile($stream);
        }

        if (class_exists(SlimUploadedFileFactory::class)) {
            $stream = $this->buildStream($data);

            return (new SlimUploadedFileFactory())->createUploadedFile($stream);
        }

        if (class_exists(FatfreeFactory::class)) {
            $stream = $this->buildStream($data);

            return (new FatfreeFactory())->createUploadedFile($stream);
        }

        throw new \RuntimeException('Could not create Stream. Check your config');
    }
}
