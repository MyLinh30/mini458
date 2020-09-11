<?php

namespace Magenest\QuickBooksDesktop\Plugin\Request;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class CsrfValidator
{
    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @param FormKeyValidator $formKeyValidator
     * @param RedirectFactory $redirectFactory
     * @param AppState $appState
     */
    public function __construct(
        FormKeyValidator $formKeyValidator,
        RedirectFactory $redirectFactory,
        AppState $appState
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->redirectFactory = $redirectFactory;
        $this->appState = $appState;
    }

    /**
     * @param HttpRequest $request
     * @param ActionInterface $action
     *
     * @return bool
     */
    private function validateRequest(
        HttpRequest $request,
        ActionInterface $action
    ) {
        $valid = null;
        if ($action instanceof CsrfAwareActionInterface) {
            $valid = $action->validateForCsrf($request);
        }
        if ($valid === null) {
            $valid = !$request->isPost()
                || $request->isAjax()
                || $this->formKeyValidator->validate($request);
        }

        return $valid;
    }

    /**
     * @param HttpRequest $request
     * @param ActionInterface $action
     *
     * @return InvalidRequestException
     */
    private function createException(
        HttpRequest $request,
        ActionInterface $action
    ) {
        $exception = null;
        if ($action instanceof CsrfAwareActionInterface) {
            $exception = $action->createCsrfValidationException($request);
        }
        if (!$exception) {
            $response = $this->redirectFactory->create()
                ->setRefererOrBaseUrl()
                ->setHttpResponseCode(302);
            $messages = [
                new Phrase('Invalid Form Key. Please refresh the page.'),
            ];
            $exception = new InvalidRequestException($response, $messages);
        }

        return $exception;
    }

    /**
     * @param \Magento\Framework\App\Request\CsrfValidator $object
     * @param callable $proceed
     * @param RequestInterface $request
     * @param ActionInterface $action
     * @throws InvalidRequestException
     */
    public function aroundValidate(
        \Magento\Framework\App\Request\CsrfValidator $object,
        callable $proceed,
        RequestInterface $request,
        ActionInterface $action
    ) {
        $redirectUrl = $_SERVER['REQUEST_URI'];
        if (strpos($redirectUrl, 'qbdesktop/connection') === false) {
            try {
                $areaCode = $this->appState->getAreaCode();
            } catch (LocalizedException $exception) {
                $areaCode = null;
            }
            if ($request instanceof HttpRequest
                && in_array(
                    $areaCode,
                    [Area::AREA_FRONTEND, Area::AREA_ADMINHTML],
                    true
                )
            ) {
                $valid = $this->validateRequest($request, $action);
                if (!$valid) {
                    $exception = $this->createException($request, $action);
                    throw $exception;
                }
            }
        } else {
        }
    }
}
