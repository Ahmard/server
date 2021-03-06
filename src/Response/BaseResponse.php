<?php


namespace Remcodex\Server\Response;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Nette\Utils\Json;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Remcodex\Server\ResponseGenerator;
use Remcodex\Server\View\View;

abstract class BaseResponse extends Response implements ResponseInterface
{
    protected const STREAM_FILE = 'php://memory';

    protected StreamInterface $body;

    protected string $jsonData;

    protected ?string $view = null;

    protected ResponseInterface $respondWith;


    public static function create(): ResponseInterface
    {
        return new static();
    }

    public function withJson($arrayWithJson): ResponseInterface
    {
        $this->jsonData = Json::encode($arrayWithJson);
        return $this;
    }

    public function withView(string $viewFile, array $viewData = []): ResponseInterface
    {
        $this->view = View::load($viewFile, $viewData);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withResponse(ResponseInterface $response): ResponseInterface
    {
        $this->respondWith = $response;
        return $response;
    }

    public function send(ServerRequestInterface $request)
    {
        if ($this->hasResponse()) {
            $this->getResponse()->send($request);
            exit();
        }

        ResponseGenerator::generate($this)->send($request);
    }

    public function hasResponse(): bool
    {
        return isset($this->respondWith);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->respondWith;
    }

    public function getView(): ?string
    {
        return $this->view;
    }

    public function getJson(): string
    {
        return $this->jsonData;
    }

    public function hasView(): bool
    {
        return isset($this->view);
    }

    /**
     * Create stream of string
     * @param string $body
     * @return ResponseInterface
     */
    protected function writeBodyStream(string $body): ResponseInterface
    {
        $stream = new Stream(self::STREAM_FILE, 'w+');
        $stream->write($body);
        $stream->rewind();
        return parent::withBody($stream);
    }
}