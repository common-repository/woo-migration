<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgCustom
{
    const DISPLAY_CONFIG = false;
    const DISPLAY_CONFIRM = false;
    const DISPLAY_IMPORT = false;
    const TAX_CHECK = false;
    const TAX_ID = false;
    const TAX_CONVERT = false;
    const TAX_IMPORT = false;
    const TAX_AFTER_SAVE = false;
    const TAX_ADDITION = false;
    const MANUFACTURER_CHECK = false;
    const MANUFACTURER_ID = false;
    const MANUFACTURER_CONVERT = false;
    const MANUFACTURER_IMPORT = false;
    const MANUFACTURER_AFTER_SAVE = false;
    const MANUFACTURER_ADDITION = false;
    const CATEGORY_CHECK = false;
    const CATEGORY_ID = false;
    const CATEGORY_CONVERT = false;
    const CATEGORY_IMPORT = false;
    const CATEGORY_AFTER_SAVE = false;
    const CATEGORY_ADDITION = false;
    const PRODUCT_CHECK = false;
    const PRODUCT_ID = false;
    const PRODUCT_CONVERT = false;
    const PRODUCT_IMPORT = false;
    const PRODUCT_AFTER_SAVE = false;
    const PRODUCT_ADDITION = false;
    const CUSTOMER_CHECK = false;
    const CUSTOMER_ID = false;
    const CUSTOMER_CONVERT = false;
    const CUSTOMER_IMPORT = false;
    const CUSTOMER_AFTER_SAVE = false;
    const CUSTOMER_ADDITION = false;
    const ORDER_CHECK = false;
    const ORDER_ID = false;
    const ORDER_CONVERT = false;
    const ORDER_IMPORT = false;
    const ORDER_AFTER_SAVE = false;
    const ORDER_ADDITION = false;
    const REVIEW_CHECK = false;
    const REVIEW_ID = false;
    const REVIEW_CONVERT = false;
    const REVIEW_IMPORT = false;
    const REVIEW_AFTER_SAVE = false;
    const REVIEW_ADDITION = false;
    const PAGE_CHECK = false;
    const PAGE_ID = false;
    const PAGE_CONVERT = false;
    const PAGE_IMPORT = false;
    const PAGE_AFTER_SAVE = false;
    const PAGE_ADDITION = false;
    const POST_CATEGORY_CHECK = false;
    const POST_CATEGORY_ID = false;
    const POST_CATEGORY_CONVERT = false;
    const POST_CATEGORY_IMPORT = false;
    const POST_CATEGORY_AFTER_SAVE = false;
    const POST_CATEGORY_ADDITION = false;
    const POST_CHECK = false;
    const POST_ID = false;
    const POST_CONVERT = false;
    const POST_IMPORT = false;
    const POST_AFTER_SAVE = false;
    const POST_ADDITION = false;
    const COMMENT_CHECK = false;
    const COMMENT_ID = false;
    const COMMENT_CONVERT = false;
    const COMMENT_IMPORT = false;
    const COMMENT_AFTER_SAVE = false;
    const COMMENT_ADDITION = false;

    public function displayConfigCustom($cart){
        return false;
    }

    public function displayConfirmCustom($cart){
        return false;
    }

    public function displayImportCustom($cart){
        return false;
    }

    public function prepareImportTaxesCustom($cart){
        return false;
    }

    public function getTaxesExtQueryCustom($cart, $taxes){
        return false;
    }

    public function getTaxesExtRelQueryCustom($cart, $taxes, $taxesExt){
        return false;
    }

    public function getTaxIdCustom($cart, $tax, $taxesExt){
        return false;
    }

    public function checkTaxImportCustom($cart, $tax, $taxesExt){
        return false;
    }

    public function convertTaxCustom($cart, $tax, $taxExt){
        return false;
    }

    public function importTaxCustom($cart, $data, $tax, $taxesExt){
        return false;
    }

    public function additionTaxCustom($cart, $convert, $tax, $taxesExt){
        return false;
    }

    public function afterSaveTaxCustom($cart, $tax_id_desc, $convert, $tax, $taxesExt){
        return false;
    }

    public function prepareImportManufacturersCustom($cart){
        return false;
    }

    public function getManufacturersExtQueryCustom($cart, $manufacturers){
        return false;
    }

    public function getManufacturersExtRelQueryCustom($cart, $manufacturers, $manufacturersExt){
        return false;
    }

    public function getManufacturerIdCustom($cart, $manufacturer, $manufacturersExt){
        return false;
    }

    public function checkManufacturerImportCustom($cart, $manufacturer, $manufacturersExt){
        return false;
    }

    public function convertManufacturerCustom($cart, $manufacturer, $manufacturersExt){
        return false;
    }

    public function importManufacturerCustom($cart, $data, $manufacturer, $manufacturersExt){
        return false;
    }

    public function additionManufacturerCustom($cart, $convert, $manufacturer, $manufacturersExt){
        return false;
    }

    public function afterSaveManufacturerCustom($cart, $manufacturer_id_desc, $convert, $manufacturer, $manufacturersExt){
        return false;
    }

    public function prepareImportCategoriesCustom($cart){
        return false;
    }

    public function getCategoriesExtQueryCustom($cart, $categories){
        return false;
    }

    public function getCategoriesExtRelQueryCustom($cart, $categories, $categoriesExt){
        return false;
    }

    public function getCategoryIdCustom($cart, $category, $categoriesExt){
        return false;
    }

    public function checkCategoryImportCustom($cart, $category, $categoriesExt){
        return false;
    }

    public function convertCategoryCustom($cart, $category, $categoriesExt){
        return false;
    }

    public function importCategoryCustom($cart, $data, $category, $categoriesExt){
        return false;
    }

    public function additionCategoryCustom($cart, $convert, $category, $categoriesExt){
        return false;
    }

    public function afterSaveCategoryCustom($cart, $category_id_desc, $convert, $category, $categoriesExt){
        return false;
    }

    public function prepareImportProductsCustom($cart){
        return false;
    }

    public function getProductsExtQueryCustom($cart, $products){
        return false;
    }

    public function getProductsExtRelQueryCustom($cart, $products, $productsExt){
        return false;
    }

    public function getProductIdCustom($cart, $product, $productsExt){
        return false;
    }

    public function checkProductImportCustom($cart, $product, $productsExt){
        return false;
    }

    public function convertProductCustom($cart, $product, $productsExt){
        return false;
    }

    public function importProductCustom($cart, $data, $product, $productsExt){
        return false;
    }

    public function afterSaveProductCustom($cart, $product_id_desc, $convert, $product, $productsExt){
        return false;
    }

    public function additionProductCustom($cart, $convert, $product, $productsExt){
        return false;
    }

    public function prepareImportCustomersCustom($cart){
        return false;
    }

    public function getCustomersExtQueryCustom($cart, $customers){
        return false;
    }

    public function getCustomerExtRelQueryCustom($cart, $customers, $customersExt){
        return false;
    }

    public function getCustomerIdCustom($cart, $customer, $customersExt){
        return false;
    }

    public function checkCustomerImportCustom($cart, $customer, $customersExt){
        return false;
    }

    public function convertCustomerCustom($cart, $customer, $customersExt){
        return false;
    }

    public function importCustomerCustom($cart, $data, $customer, $customersExt){
        return false;
    }

    public function afterSaveCustomerCustom($cart, $customer_id_desc, $convert, $customer, $customersExt){
        return false;
    }

    public function additionCustomerCustom($cart, $convert, $customer, $customersExt){
        return false;
    }

    public function prepareImportOrdersCustom($cart){
        return false;
    }

    public function getOrdersExtQueryCustom($cart, $orders){
        return false;
    }

    public function getOrdersExtRelQueryCustom($cart, $orders, $ordersExt){
        return false;
    }

    public function getOrderIdCustom($cart, $order, $ordersExt){
        return false;
    }

    public function checkOrderImportCustom($cart, $order, $ordersExt){
        return false;
    }

    public function convertOrderCustom($cart, $order, $ordersExt){
        return false;
    }

    public function importOrderCustom($cart, $data, $order, $ordersExt){
        return false;
    }

    public function afterSaveOrderCustom($cart, $order_id_desc, $convert, $order, $ordersExt){
        return false;
    }

    public function additionOrderCustom($cart, $convert, $order, $ordersExt){
        return false;
    }

    public function prepareImportReviewsCustom($cart){
        return false;
    }

    public function getReviewsExtQueryCustom($cart, $reviews){
        return false;
    }

    public function getReviewsExtRelQueryCustom($cart, $reviews, $reviewsExt){
        return false;
    }

    public function getReviewIdCustom($cart, $review, $reviewsExt){
        return false;
    }

    public function checkReviewImportCustom($cart, $review, $reviewsExt){
        return false;
    }

    public function convertReviewCustom($cart, $review, $reviewsExt){
        return false;
    }

    public function importReviewCustom($cart, $data, $review, $reviewsExt){
        return false;
    }

    public function afterSaveReviewCustom($cart, $review_id_desc, $convert, $review, $reviewsExt){
        return false;
    }

    public function additionReviewCustom($cart, $convert, $review, $reviewsExt){
        return false;
    }

    public function prepareImportPagesCustom($cart){
    return false;
}

    public function getPagesExtQueryCustom($cart, $pages){
        return false;
    }

    public function getPagesExtRelQueryCustom($cart, $pages, $pagesExt){
        return false;
    }

    public function getPageIdCustom($cart, $page, $pagesExt){
        return false;
    }

    public function checkPageImportCustom($cart, $page, $pagesExt){
        return false;
    }

    public function convertPageCustom($cart, $page, $pagesExt){
        return false;
    }

    public function importPageCustom($cart, $data, $page, $pagesExt){
        return false;
    }

    public function afterSavePageCustom($cart, $page_id_desc, $convert, $page, $pagesExt){
        return false;
    }

    public function additionPageCustom($cart, $convert, $page, $pagesExt){
        return false;
    }

    public function prepareImportPostCatCustom($cart){
        return false;
    }

    public function getPostCatExtQueryCustom($cart, $postCat){
        return false;
    }

    public function getPostCatExtRelQueryCustom($cart, $postCat, $postCatExt){
        return false;
    }

    public function getPostCatIdCustom($cart, $postCat, $postCatExt){
        return false;
    }

    public function checkPostCatImportCustom($cart, $postCat, $postCatExt){
        return false;
    }

    public function convertPostCatCustom($cart, $postCat, $postCatExt){
        return false;
    }

    public function importPostCatCustom($cart, $data, $postCat, $postCatExt){
        return false;
    }

    public function afterSavePostCatCustom($cart, $postCat_id_desc, $convert, $postCat, $postCatExt){
        return false;
    }

    public function additionPostCatCustom($cart, $convert, $postCat, $postCatExt){
        return false;
    }

    public function prepareImportPostCustom($cart){
        return false;
    }

    public function getPostsExtQueryCustom($cart, $posts){
        return false;
    }

    public function getPostsExtRelQueryCustom($cart, $posts, $postExt){
        return false;
    }

    public function getPostIdCustom($cart, $post, $postExt){
        return false;
    }

    public function checkPostImportCustom($cart, $post, $postExt){
        return false;
    }

    public function convertPostCustom($cart, $post, $postExt){
        return false;
    }

    public function importPostCustom($cart, $data, $post, $postExt){
        return false;
    }

    public function afterSavePostCustom($cart, $post_id_desc, $convert, $post, $postExt){
        return false;
    }

    public function additionPostCustom($cart, $convert, $post, $postExt){
        return false;
    }

    public function prepareImportCommentCustom($cart){
        return false;
    }

    public function getCommentsExtQueryCustom($cart, $comments){
        return false;
    }

    public function getCommentsExtRelQueryCustom($cart, $comments, $commentExt){
        return false;
    }

    public function getCommentIdCustom($cart, $comment, $commentExt){
        return false;
    }

    public function checkCommentImportCustom($cart, $comment, $commentExt){
        return false;
    }

    public function convertCommentCustom($cart, $comment, $commentExt){
        return false;
    }

    public function importCommentCustom($cart, $data, $comment, $commentExt){
        return false;
    }

    public function afterSaveCommentCustom($cart, $comment_id_desc, $convert, $comment, $commentExt){
        return false;
    }

    public function additionCommentCustom($cart, $convert, $comment, $commentExt){
        return false;
    }
}