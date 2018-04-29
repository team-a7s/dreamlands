<?php

declare(strict_types=1);

namespace Kadath\Middlewares;

use Kadath\Database\Records\MemberRecord;
use Kadath\Database\Records\UserRecord;
use Kadath\Database\Repositories\UserRepo;
use Kadath\Exceptions\KadathException;
use Kadath\Utility\IdGeneratorInterface;
use Lit\Air\Injection\SetterInjector;
use Lit\Griffin\ExportableInterface;
use Lit\Griffin\ExportableTrait;
use Lit\Nexus\Derived\ObjectKeyValue;
use Lit\Nexus\Interfaces\KeyValueInterface;
use Lit\Nimo\AbstractMiddleware;
use Lit\Nimo\Traits\MiddlewareTrait;
use Psr\Http\Message\ResponseInterface;

class SessionMiddleware extends AbstractMiddleware implements ExportableInterface
{
    use MiddlewareTrait;
    use ExportableTrait;

    const SETTER_INJECTOR = SetterInjector::class;
    const HEADER_NAME = 'X-Kadath-Token';
    const OBJECT_TYPE = 'session';
    const SESSION_USER = 'user';
    const SESSION_MEMBER = 'member';
    const SESSION_OAUTH_STATE = 'oauth_state';
    const SESSION_ACCESS_TOKEN = 'access_token';
    const SESSION_TURING = 'turing';

    /**
     * @var UserRecord|bool
     */
    protected $currentUser;
    /**
     * @var MemberRecord|bool
     */
    protected $currentMember;
    /**
     * @var ObjectKeyValue
     */
    protected $session;
    /**
     * @var string
     */
    protected $sid;
    /**
     * @var KeyValueInterface
     */
    protected $storage;


    /**
     * @var UserRepo
     */
    protected $userRepo;

    public function injectUserRepo(UserRepo $userRepo)
    {
        $this->userRepo = $userRepo;
        return $this;
    }


    /**
     * @var IdGeneratorInterface
     */
    protected $idGenerator;

    public function injectIdGenerator(IdGeneratorInterface $idGenerator)
    {
        $this->idGenerator = $idGenerator;
        return $this;
    }


    public function __construct(KeyValueInterface $storage)
    {
        $this->storage = $storage;
    }

    public function exportArray(): array
    {
        return [
            'token' => $this->sid,
            'currentUser' => function () {
                return $this->getCurrentUser();
            }
        ];
    }

    /**
     * @return KeyValueInterface|null
     */
    public function getSession(): ?KeyValueInterface
    {
        return $this->session;
    }

    public function createSession()
    {
        $sessionContent = new \stdClass();
        if ($this->sid) {
            $sessionContent->{self::SESSION_TURING} = $this->session->get(self::SESSION_TURING);
            $this->storage->delete($this->sid);
        }

        do {
            $sid = $this->idGenerator->generate();
        } while ($this->storage->exists($sid));

        $this->sid = $sid;
        $this->storage->set($sid, json_encode($sessionContent));
        $this->session = ObjectKeyValue::wrap($sessionContent);
    }

    public function sessionExist(string $sid): bool
    {
        return $this->storage->exists($sid);
    }

    public function initSession(string $sid)
    {
        assert($this->sessionExist($sid));

        $this->sid = $sid;
        $content = json_decode($this->storage->get($sid));

        assert(json_last_error() === JSON_ERROR_NONE);

        $this->session = ObjectKeyValue::wrap($content);
    }

    /**
     * @return string
     */
    public function getSid(): ?string
    {
        return $this->sid;
    }

    protected function saveSession()
    {
        if (isset($this->session)) {
            $this->storage->set($this->sid, json_encode($this->session->getContent()));
        }
    }

    protected function main(): ResponseInterface
    {
        $this->attachToRequest();

        $sid = $this->request->getHeaderLine(self::HEADER_NAME);
        if ($sid && $this->sessionExist($sid)) {
            $this->initSession($sid);
        }

        $response = $this->delegate();
        $this->saveSession();

        return $response;
    }

    public function getCurrentUser(): ?UserRecord
    {
        if (!$this->session) {
            return null;
        }

        if (!isset($this->currentUser)) {
            if (!$this->session->exists(self::SESSION_USER)) {
                $this->currentUser = false;
            } else {
                $sessionUser = $this->session->get(self::SESSION_USER);
                $user = $this->userRepo->find($sessionUser->id);
                $this->currentUser = $user->hash === $sessionUser->hash ? $user : false;
            }
        }

        if (!$this->currentUser) {
            return null;
        }

        return $this->currentUser;
    }

    public function needLogin()
    {
        if (!$this->getCurrentUser()) {
            throw KadathException::auth('need login');
        }
    }

    public function setLogin(UserRecord $userRecord)
    {
        $this->currentUser = $userRecord;
        $this->session->set(self::SESSION_USER, (object)[
            'id' => $userRecord->id,
            'hash' => $userRecord->hash,
        ]);
    }

    public function setLoginMember(MemberRecord $memberRecord)
    {
        $this->currentMember = $memberRecord;
        $this->session->set(self::SESSION_MEMBER, (object)[
            'id' => $memberRecord->id,
        ]);
    }
}
