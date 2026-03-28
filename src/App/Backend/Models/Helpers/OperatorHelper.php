<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Backend\Models\Helpers;

use App\Backend\Models\Facades\OperatorFacade;
use App\Backend\Repositories\OperatorRepository;
use App\Backend\Repositories\UserRepository;
use App\Frontend\Services\OperatorService;
use App\Frontend\Services\UserService;
use App\Util\Handlers\SearchHandler;
use InvalidArgumentException;
use LogicException;

trait OperatorHelper
{
    public function __construct(
        protected ?OperatorFacade $operatorFacade = null
    ) {}

    private function getFacade(): OperatorFacade
    {
        return new OperatorFacade(
            new OperatorService(
                new OperatorRepository,
                new UserService(
                    new UserRepository,
                    new SearchHandler
                )
            )
        );
    }

    public function setFacade(): self
    {
        $this->operatorFacade = $this->getFacade();
        return $this;
    }

    public function unsetFacade(): self
    {
        $this->operatorFacade = null;
        return $this;
    }

    protected function ensureFacade(): void
    {
        if (! isset($this->operatorFacade)) {
            throw new LogicException(
                'OperatorFacade must be set before calling CRUD operations!'
            );
        }
    }

    /**
     * -------------------- CRUD --------------------
     */

    /**
     * createByUserId
     *
     * @param  string $userId
     * @return null|self
     */
    public function createByUserId(string $userId): ?self
    {
        // $this->operatorService->createByUserId($userId);
        // return $this->read();
        $this->ensureFacade();

        $created = $this->operatorFacade->createByUserId($userId);
        return $created ? $created->hydrate($created) : null;
    }

    /**
     * reset
     *
     * @return self
     */
    public function reset(): self
    {
        $this->id    = null;
        $this->email = null;
        $this->auth  = null;

        return $this;
    }

    /**
     * create
     *
     * @param  string $userId
     * @return null|self
     */
    public function create(): ?self
    {
        $this->ensureFacade();

        $this->createByUserId($this->id);
        return $this->read();
    }

    /**
     * read
     *
     * @return null|self
     */
    public function read(): ?self
    {
        if ($this->id === null) {
            throw new InvalidArgumentException('Cannot read an operator without an valid id.');
        }

        $storedOperator = $this->operatorFacade->readById($this->id);
        if ($storedOperator === null) {
            return null;
        }

        $this->hydrate($storedOperator);

        return $this;
    }

    /**
     * updateEmail
     *
     * @return self
     */
    public function updateEmail(): self
    {
        $this->ensureFacade();

        $storedOperator = $this->operatorFacade->updateEmail($this);

        $this->hydrate($storedOperator);

        return $this;
    }

    /**
     * updateAuth
     *
     * @return self
     */
    public function updateAuth(): self
    {
        $this->ensureFacade();

        $storedOperator = $this->operatorFacade->updateAuth($this);

        $this->hydrate($storedOperator);

        return $this;
    }

    /**
     * delete
     *
     * @return bool true if deletion succeeded, false otherwise
     */
    public function delete(): bool
    {
        $this->ensureFacade();

        return $this->operatorFacade->delete($this);
    }

    /**
     * hydrate
     *
     * @param  null|self $storedOperator
     * @return void
     */
    public function hydrate(?self $storedOperator): void
    {
        if ($storedOperator === null) {
            $this->reset();
        } else {
            $this->id    = $storedOperator->getId();
            $this->email = $storedOperator->getEmail();
            $this->auth  = $storedOperator->getAuth();
        }
    }
}
