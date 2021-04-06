<?php

namespace App\Controller;

use App\Exception\Services\CreateNewProductException;
use App\Exception\Services\ResourceNotFoundException;
use App\Form\ProductType;
use App\Services\ApiResponseFormatter;
use App\Services\ProductDefinitionDto;
use App\Services\ProductService;
use App\Services\StatsDClient;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductApiController extends AbstractApiController
{
    private ProductService $productService;
    private ApiResponseFormatter $responseFormatter;
    private LoggerInterface $logger;
    private StatsDClient $stats;

    public const MAX_PERPAGE = 3;
    public const PERPAGE_KEY = 'perpage';
    public const PAGE_KEY = 'page';

    public const TIMING_PRODUCT_LIST = 'product_list';
    public const TIMING_GET_PRODUCT = 'product_get';
    public const TIMING_CREATE_PRODUCT = 'product_create';
    public const TIMING_REMOVE_PRODUCT = 'product_remove';
    public const TIMING_UPDATE_PRODUCT = 'product_update';

    public function __construct(
        ProductService $productService,
        ApiResponseFormatter $responseFormatter,
        LoggerInterface $logger,
        StatsDClient $stats
    ) {
        $this->productService = $productService;
        $this->responseFormatter = $responseFormatter;
        $this->logger = $logger;
        $this->stats = $stats;
    }


    public function getProductList(Request $request): Response
    {
        $this->stats->startTiming(self::TIMING_PRODUCT_LIST);
        $perpage = (int)$request->get(self::PERPAGE_KEY, 3);
        $page = (int)$request->get(self::PAGE_KEY, 1);

        if (self::MAX_PERPAGE < $perpage) {
            return $this->respond(['error' => 'Maximum perpage value exceed'], Response::HTTP_BAD_REQUEST);
        }

        $offset = ($page - 1) * $perpage;
        $productList = $this->productService->getProductList($offset, $perpage);

        $data = $this->responseFormatter->formatProductList($productList);

        $this->stats->endTiming(self::TIMING_PRODUCT_LIST);
        return $this->handleView($this->view($data, Response::HTTP_OK));
    }

    public function getProduct(int $productId): Response
    {
        $this->stats->startTiming(self::TIMING_GET_PRODUCT);
        try {
            $product = $this->productService->getProduct($productId);
            $data = $this->responseFormatter->formatProduct($product);

            $this->stats->endTiming(self::TIMING_GET_PRODUCT);
            return $this->handleView($this->view($data, Response::HTTP_OK));
        } catch (ResourceNotFoundException) {
            return $this->respond('ProductNotFound', Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            $uniqueErrorId = uniqid();
            $this->logger->error(
                'Unexpected exception in ' . __METHOD__ . ': ' . $e->getMessage(),
                [
                    'trace' => $e->getTrace(),
                    'productId' => $productId,
                    'errorId' => $uniqueErrorId
                ]
            );

            return $this->respond(
                "Internal server error. Id: $uniqueErrorId",
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function removeProduct($productId): Response
    {
        $this->stats->startTiming(self::TIMING_REMOVE_PRODUCT);
        try {
            $this->productService->removeProduct($productId);
            $this->stats->endTiming(self::TIMING_REMOVE_PRODUCT);

            return $this->handleView($this->view('Ok', Response::HTTP_OK));
        } catch (ResourceNotFoundException) {
            return $this->respond('ProductNotFound', Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            $uniqueErrorId = uniqid();
            $this->logger->error(
                'Unexpected exception in ' . __METHOD__ . ': ' . $e->getMessage(),
                [
                    'trace' => $e->getTrace(),
                    'productId' => $productId,
                    'errorId' => $uniqueErrorId
                ]
            );

            return $this->respond(
                "Internal server error. Id: $uniqueErrorId",
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }


    public function createProduct(Request $request): Response
    {
        return $this->processProduct($request);
    }

    public function updateProduct(Request $request, int $productId): Response
    {
        try {
            $existingProduct = $this->productService->getProduct($productId);
        }  catch (ResourceNotFoundException) {
            return $this->respond('ProductNotFound', Response::HTTP_NOT_FOUND);
        }

        return $this->processProduct($request, $existingProduct);
    }


    private function processProduct(Request $request, $existingProduct = null): Response
    {
        if (null === $existingProduct) {
            $this->stats->startTiming(self::TIMING_CREATE_PRODUCT);
        } else {
            $this->stats->startTiming(self::TIMING_UPDATE_PRODUCT);
        }

        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(ProductType::class, new ProductDefinitionDto());
        $form->submit($data);

        if (false === $form->isValid()) {
            return $this->respond(
                $form->getErrors(),
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $product = $this->productService->storeProduct($form->getData(), $existingProduct);

            if (null === $existingProduct) {
                $this->stats->endTiming(self::TIMING_CREATE_PRODUCT);
            } else {
                $this->stats->endTiming(self::TIMING_UPDATE_PRODUCT);
            }
        } catch (CreateNewProductException $e) {
            return $this->respond(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (Exception $e) {
            $uniqueErrorId = uniqid();
            $this->logger->error(
                'Unexpected exception in ' . __METHOD__ . ': ' . $e->getMessage(),
                [
                    'trace' => $e->getTrace(),
                    'formData' => $data,
                    'errorId' => $uniqueErrorId
                ]
            );

            return $this->respond(
                "Internal server error. Id: $uniqueErrorId",
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->respond(
            $this->responseFormatter->formatProduct($product),
            Response::HTTP_CREATED
        );
    }
}
