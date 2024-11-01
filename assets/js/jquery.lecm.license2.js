/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

(function($){
    $.extend({
        LECM: function(options) {
            var defaults = {
                urlRun: '',
                menuCart: '#menu-cart',
                menuConfig: '#menu-config',
                menuConfirm: '#menu-confirm',
                formCartWrap: '#lecm-cart',
                formCart: '#form-cart',
                formCartLoading: '#form-cart-loading',
                formCartSubmit: '#form-cart-submit',
                formConfigWrap: '#lecm-config',
                formConfig: '#form-config',
                formConfigLoading: '#form-config-loading',
                formConfigSubmit: '#form-config-submit',
                formConfigBack: '#form-config-back',
                formConfirmWrap: '#lecm-confirm',
                formConfirm: '#form-confirm',
                formConfirmLoading: '#form-confirm-loading',
                formConfirmSubmit: '#form-confirm-submit',
                formConfirmBack: '#form-confirm-back',
                formImportWrap: '#lecm-import',
                formImport: '#form-import',
                formImportLoading: '#form-import-loading',
                formImportSubmit: '#form-import-submit',
                formResumeWrap: '#lecm-resume',
                formResume: '#form-resume',
                formResumeLoading: '#form-resume-loading',
                formResumeSubmit: '#form-resume-submit',
                errorMsg: 'Request timeout or server isn\'t responding, please reload the page.',
                msgTryError: '<p class="error">Request timeout or server isn\'t responding, please try again.</p>',
                msgTryWarning: '<p class="warning">Please try again.</p>',
                msgTryImport: '<p class="success"> - Resuming import ...</p>',
                importText: 'Imported',
                errorText: 'Errors',
                processTaxes: '#process-taxes',
                processManufacturers: '#process-manufacturers',
                processCategories: '#process-categories',
                processProducts: '#process-products',
                processCustomers: '#process-customers',
                processOrders: '#process-orders',
                processReviews: '#process-reviews',
                processPages: '#process-pages',
                processPostCat: '#process-postCat',
                processPosts: '#process-posts',
                processComments: '#process-comments',
                tryImportTaxes: '#try-import-taxes',
                tryImportManufacturers: '#try-import-manufacturers',
                tryImportCategories : '#try-import-categories',
                tryImportProducts : '#try-import-products',
                tryImportCustomers : '#try-import-customers',
                tryImportOrders : '#try-import-orders',
                tryImportReviews: '#try-import-reviews',
                tryImportPages: '#try-import-pages',
                tryImportPostCat: '#try-import-postCat',
                tryImportPosts: '#try-import-posts',
                tryImportComments: '#try-import-comments',
                tryClearShop: '#try-clear-shop',
                fnResume: 'importTaxes',
                clearCurrentData: '#clear-data',
                clearDataLoading: '#clear-data-loading',
                tryClearData: '#try-clear-data',
                timeDelay: 2000,
                autoRetry: 30000

            };
            var settings = $.extend({}, defaults, options);

            function enabledMenu(elm) {
                $(elm).addClass('finished');
            }

            function disabledMenu(elm) {
                $(elm).removeClass('finished');
            }

            function validationEntitySelect() {
                if ($('#entity-section input:checkbox:checked').length > 0) {
                    $('#error-entity').fadeOut();
                    return true;
                } else {
                    $('#error-entity').fadeIn();
                    return false;
                }
            }

            function checkOptionDuplicate(elm) {
                var check = new Array();
                $(elm).each(function(index, value) {
                    var element = $(value);
                    check[index] = element.val();
                });
                var result = true;
                check.forEach(function(value, index) {
                    check.forEach(function(value_tmp, index_tmp) {
                        if (value_tmp === value && index !== index_tmp) {
                            result = false;
                        }
                    });
                });
                return result;
            }

            function checkSelectLangDuplicate() {
                var select = '.lang-check select';
                var check = checkOptionDuplicate(select);
                if (check === true) {
                    $('#error-lang').fadeOut();
                } else {
                    $('#error-lang').fadeIn();
                }
                return check;
            }

            function createLecmCookie(value) {
                var date = new Date();
                date.setTime(date.getTime() + (24 * 60 * 60 * 1000));
                var expires = "; expires=" + date.toGMTString();
                document.cookie = "le_cart_migration_run=" + value + expires + "; path=/";
            }

            function getLecmCookie() {
                var nameEQ = "le_cart_migration_run=";
                var ca = document.cookie.split(';');
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) === ' ')
                        c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) === 0)
                        return c.substring(nameEQ.length, c.length);
                }
                return null;
            }

            function deleteLecmCookie() {
                var date = new Date();
                date.setTime(date.getTime() + (-1 * 24 * 60 * 60 * 1000));
                var expires = "; expires=" + date.toGMTString();
                document.cookie = "le_cart_migration_run=" + expires + "; path=/";
            }

            function checkLecmCookie() {
                var check = getLecmCookie();
                var result = false;
                if (check === '1') {
                    result = true;
                }
                return result;
            }

            function showTryImport(elm) {
                var element = $(elm);
                if (element.length > 0) {
                    element.find('.try-import').show();
                    deleteLecmCookie();
                }
            }

            function hideTryImport(elm){
                var element = $(elm).find('.try-import');
                if(element.length !== 0){
                    showCslMsg(settings.msgTryImport);
                    element.hide();
                }
                createLecmCookie(1);
            }

            function showProcessBar(elm, total, imported, error, point) {
                var element = $(elm);
                if (element.length > 0) {
                    showProcessBarWidth(element, point);
                    showProcessBarConsole(element, total, imported, error);
                } else {
                    return false;
                }
            }

            function showProcessBarWidth(element, point) {
                var pbw = element.find('.process-bar-width');
                if (pbw.length !== 0 && point !== null) {
                    pbw.css({
                        'display': 'block',
                        'width': point + '%'
                    });
                } else {
                    return false;
                }
            }

            function showProcessBarConsole(element, total, imported, error) {
                var pbc = element.find('.console-log');
                if (pbc.length !== 0) {
                    var html = settings.importText + ': ' + imported + '/' + total + ', '+ settings.errorText + ': ' + error;
                    pbc.show();
                    pbc.html(html);
                } else {
                    return false;
                }
            }

            function showCslMsg(msg) {
                var csl = $('#lecm-csl-import');
                if (csl.length > 0) {
                    csl.append(msg);
                    csl.animate({scrollTop: csl.prop("scrollHeight")});
                }
            }

            function clearShop(){
                $('#loading-clear').show();
                $.ajax({
                    url: settings.urlRun,
                    type: 'POST',
                    data: {
                        action: 'le_cart_migration',
                        process: 'clear'
                    },
                    dataType: 'json',
                    success: function(response, textStatus, jqXHR) {
                        if (response.msg !== '') {
                            showCslMsg(response.msg);
                        }
                        if (response.result === 'success') {
                            $('#loading-clear').hide();
                            setTimeout(importTaxes, settings.timeDelay);
                        } else if (response.result === 'error') {
                            $('#loading-clear').hide();
                            $(settings.tryClearShop).show();
                        } else if (response.result === 'process') {
                            setTimeout(clearShop, settings.timeDelay);
                        } else {
                            $('#loading-clear').hide();
                            setTimeout(importTaxes, settings.timeDelay);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $('#loading-clear').hide();
                        showCslMsg('<p class="error">' + settings.msgTryImport + '</p>');
                        $(settings.tryClearShop).show();
                    }
                });
            }

            function importTaxes() {
                $.ajax({
                    url: settings.urlRun,
                    type: 'POST',
                    data: {
                        action: 'le_cart_migration',
                        process: 'import',
                        type: 'taxes'
                    },
                    dataType: 'json',
                    success: function(response, textStatus, jqXHR) {
                        if(!response){
                            showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                            showTryImport(settings.processTaxes);
                            autoRetry(settings.processTaxes);
                            return false;
                        }
                        if (response.msg !== '') {
                            showCslMsg(response.msg);
                        }
                        if (response.result === 'success') {
                            showProcessBar(settings.processTaxes, response.taxes.real_total, response.taxes.imported, response.taxes.error, response.taxes.point);
                            setTimeout(importManufacturers, settings.timeDelay);
                        } else if (response.result === 'error') {
                            showTryImport(settings.processTaxes);
                            autoRetry(settings.processTaxes);
                        } else if (response.result === 'process') {
                            showProcessBar(settings.processTaxes, response.taxes.real_total, response.taxes.imported, response.taxes.error, response.taxes.point);
                            setTimeout(importTaxes, settings.timeDelay);
                        } else {
                            setTimeout(importManufacturers, settings.timeDelay);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                        showTryImport(settings.processTaxes);
                        autoRetry(settings.processTaxes);
                    }
                });
            }

            function importManufacturers() {
                $.ajax({
                    url: settings.urlRun,
                    type: 'POST',
                    data: {
                        action: 'le_cart_migration',
                        process: 'import',
                        type: 'manufacturers'
                    },
                    dataType: 'json',
                    success: function(response, textStatus, jqXHR) {
                        if(!response){
                            showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                            showTryImport(settings.processManufacturers);
                            autoRetry(settings.processManufacturers);
                            return false;
                        }
                        if (response.msg !== '') {
                            showCslMsg(response.msg);
                        }
                        if (response.result === 'success') {
                            showProcessBar(settings.processManufacturers, response.manufacturers.real_total, response.manufacturers.imported, response.manufacturers.error, response.manufacturers.point);
                            setTimeout(importCategories, settings.timeDelay);
                        } else if (response.result === 'error') {
                            showTryImport(settings.processManufacturers);
                            autoRetry(settings.processManufacturers);
                        } else if (response.result === 'process') {
                            showProcessBar(settings.processManufacturers, response.manufacturers.real_total, response.manufacturers.imported, response.manufacturers.error, response.manufacturers.point);
                            setTimeout(importManufacturers, settings.timeDelay);
                        } else {
                            setTimeout(importCategories, settings.timeDelay);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                        showTryImport(settings.processManufacturers);
                        autoRetry(settings.processManufacturers);
                    }
                });
            }

            function importCategories() {
                $.ajax({
                    url: settings.urlRun,
                    type: 'POST',
                    data: {
                        action: 'le_cart_migration',
                        process: 'import',
                        type: 'categories'
                    },
                    dataType: 'json',
                    success: function(response, textStatus, jqXHR) {
                        if(!response){
                            showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                            showTryImport(settings.processCategories);
                            autoRetry(settings.processCategories);
                            return false;
                        }
                        if (response.msg !== '') {
                            showCslMsg(response.msg);
                        }
                        if (response.result === 'success') {
                            showProcessBar(settings.processCategories, response.categories.real_total, response.categories.imported, response.categories.error, response.categories.point);
                            setTimeout(importProducts, settings.timeDelay);
                        } else if (response.result === 'error') {
                            showTryImport(settings.processCategories);
                            autoRetry(settings.processCategories);
                        } else if (response.result === 'process') {
                            showProcessBar(settings.processCategories, response.categories.real_total, response.categories.imported, response.categories.error, response.categories.point);
                            setTimeout(importCategories, settings.timeDelay);
                        } else {
                            setTimeout(importProducts, settings.timeDelay);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                        showTryImport(settings.processCategories);
                        autoRetry(settings.processCategories);
                    }
                });
            }

            function importProducts() {
                $.ajax({
                    url: settings.urlRun,
                    type: 'POST',
                    data: {
                        action: 'le_cart_migration',
                        process: 'import',
                        type: 'products'
                    },
                    dataType: 'json',
                    success: function(response, textStatus, jqXHR) {
                        if(!response){
                            showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                            showTryImport(settings.processProducts);
                            autoRetry(settings.processProducts);
                            return false;
                        }
                        if (response.msg !== '') {
                            showCslMsg(response.msg);
                        }
                        if (response.result === 'success') {
                            showProcessBar(settings.processProducts, response.products.real_total, response.products.imported, response.products.error, response.products.point);
                            setTimeout(importCustomers, settings.timeDelay);
                        } else if (response.result === 'error') {
                            showTryImport(settings.processProducts);
                            autoRetry(settings.processProducts);
                        } else if (response.result === 'process') {
                            showProcessBar(settings.processProducts, response.products.real_total, response.products.imported, response.products.error, response.products.point);
                            setTimeout(importProducts, settings.timeDelay);
                        } else {
                            setTimeout(importCustomers, settings.timeDelay);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                        showTryImport(settings.processProducts);
                        autoRetry(settings.processProducts);
                    }
                });
            }

            function importCustomers() {
                $.ajax({
                    url: settings.urlRun,
                    type: 'POST',
                    data: {
                        action: 'le_cart_migration',
                        process: 'import',
                        type: 'customers'
                    },
                    dataType: 'json',
                    success: function(response, textStatus, jqXHR) {
                        if(!response){
                            showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                            showTryImport(settings.processCustomers);
                            autoRetry(settings.processCustomers);
                            return false;
                        }
                        if (response.msg !== '') {
                            showCslMsg(response.msg);
                        }
                        if (response.result === 'success') {
                            showProcessBar(settings.processCustomers, response.customers.real_total, response.customers.imported, response.customers.error, response.customers.point);
                            setTimeout(importOrders, settings.timeDelay);
                        } else if (response.result === 'error') {
                            showTryImport(settings.processCustomers);
                            autoRetry(settings.processCustomers);
                        } else if (response.result === 'process') {
                            showProcessBar(settings.processCustomers, response.customers.real_total, response.customers.imported, response.customers.error, response.customers.point);
                            setTimeout(importCustomers, settings.timeDelay);
                        } else {
                            setTimeout(importOrders, settings.timeDelay);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                        showTryImport(settings.processCustomers);
                        autoRetry(settings.processCustomers);
                    }
                });
            }

            function importOrders() {
                $.ajax({
                    url: settings.urlRun,
                    type: 'POST',
                    data: {
                        action: 'le_cart_migration',
                        process: 'import',
                        type: 'orders'
                    },
                    dataType: 'json',
                    success: function(response, textStatus, jqXHR) {
                        if(!response){
                            showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                            showTryImport(settings.processOrders);
                            autoRetry(settings.processOrders);
                            return false;
                        }
                        if (response.msg !== '') {
                            showCslMsg(response.msg);
                        }
                        if (response.result === 'success') {
                            showProcessBar(settings.processOrders, response.orders.real_total, response.orders.imported, response.orders.error, response.orders.point);
                            setTimeout(importReviews, settings.timeDelay);
                        } else if (response.result === 'error') {
                            showTryImport(settings.processOrders);
                            autoRetry(settings.processOrders);
                        } else if (response.result === 'process') {
                            showProcessBar(settings.processOrders, response.orders.real_total, response.orders.imported, response.orders.error, response.orders.point);
                            setTimeout(importOrders, settings.timeDelay);
                        } else {
                            setTimeout(importReviews, settings.timeDelay);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                        showTryImport(settings.processOrders);
                        autoRetry(settings.processOrders);
                    }
                });
            }

            function importReviews() {
                $.ajax({
                    url: settings.urlRun,
                    type: 'POST',
                    data: {
                        action: 'le_cart_migration',
                        process: 'import',
                        type: 'reviews'
                    },
                    dataType: 'json',
                    success: function(response, textStatus, jqXHR) {
                        if(!response){
                            showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                            showTryImport(settings.processReviews);
                            autoRetry(settings.processReviews);
                            return false;
                        }
                        if (response.msg !== '') {
                            showCslMsg(response.msg);
                        }
                        if (response.result === 'success') {
                            showProcessBar(settings.processReviews, response.reviews.real_total, response.reviews.imported, response.reviews.error, response.reviews.point);
                            setTimeout(importPages, settings.timeDelay);
                        } else if (response.result === 'error') {
                            showTryImport(settings.processReviews);
                            autoRetry(settings.processReviews);
                        } else if (response.result === 'process') {
                            showProcessBar(settings.processReviews, response.reviews.real_total, response.reviews.imported, response.reviews.error, response.reviews.point);
                            setTimeout(importReviews, settings.timeDelay);
                        } else {
                            setTimeout(importPages, settings.timeDelay);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                        showTryImport(settings.processReviews);
                        autoRetry(settings.processReviews);
                    }
                });
            }

            function importPages() {
                $.ajax({
                    url: settings.urlRun,
                    type: 'POST',
                    data: {
                        action: 'le_cart_migration',
                        process: 'import',
                        type: 'pages'
                    },
                    dataType: 'json',
                    success: function(response, textStatus, jqXHR) {
                        if(!response){
                            showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                            showTryImport(settings.processPages);
                            autoRetry(settings.processPages);
                            return false;
                        }
                        if (response.msg !== '') {
                            showCslMsg(response.msg);
                        }
                        if (response.result === 'success') {
                            showProcessBar(settings.processPages, response.pages.real_total, response.pages.imported, response.pages.error, response.pages.point);
                            setTimeout(importPostCat, settings.timeDelay);
                        } else if (response.result === 'error') {
                            showTryImport(settings.processPages);
                            autoRetry(settings.processPages);
                        } else if (response.result === 'process') {
                            showProcessBar(settings.processPages, response.pages.real_total, response.pages.imported, response.pages.error, response.pages.point);
                            setTimeout(importPages, settings.timeDelay);
                        } else {
                            setTimeout(importPostCat, settings.timeDelay);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                        showTryImport(settings.processPages);
                        autoRetry(settings.processPages);
                    }
                });
            }

            function importPostCat() {
                $.ajax({
                    url: settings.urlRun,
                    type: 'POST',
                    data: {
                        action: 'le_cart_migration',
                        process: 'import',
                        type: 'postCat'
                    },
                    dataType: 'json',
                    success: function(response, textStatus, jqXHR) {
                        if(!response){
                            showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                            showTryImport(settings.processPostCat);
                            autoRetry(settings.processPostCat);
                            return false;
                        }
                        if (response.msg !== '') {
                            showCslMsg(response.msg);
                        }
                        if (response.result === 'success') {
                            showProcessBar(settings.processPostCat, response.postCat.real_total, response.postCat.imported, response.postCat.error, response.postCat.point);
                            setTimeout(importPosts, settings.timeDelay);
                        } else if (response.result === 'error') {
                            showTryImport(settings.processPostCat);
                            autoRetry(settings.processPostCat);
                        } else if (response.result === 'process') {
                            showProcessBar(settings.processPostCat, response.postCat.real_total, response.postCat.imported, response.postCat.error, response.postCat.point);
                            setTimeout(importPostCat, settings.timeDelay);
                        } else {
                            setTimeout(importPosts, settings.timeDelay);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                        showTryImport(settings.processPostCat);
                        autoRetry(settings.processPostCat);
                    }
                });
            }

            function importPosts() {
                $.ajax({
                    url: settings.urlRun,
                    type: 'POST',
                    data: {
                        action: 'le_cart_migration',
                        process: 'import',
                        type: 'posts'
                    },
                    dataType: 'json',
                    success: function(response, textStatus, jqXHR) {
                        if(!response){
                            showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                            showTryImport(settings.processPosts);
                            autoRetry(settings.processPosts);
                            return false;
                        }
                        if (response.msg !== '') {
                            showCslMsg(response.msg);
                        }
                        if (response.result === 'success') {
                            showProcessBar(settings.processPosts, response.posts.real_total, response.posts.imported, response.posts.error, response.posts.point);
                            setTimeout(importComments, settings.timeDelay);
                        } else if (response.result === 'error') {
                            showTryImport(settings.processPosts);
                            autoRetry(settings.processPosts);
                        } else if (response.result === 'process') {
                            showProcessBar(settings.processPosts, response.posts.real_total, response.posts.imported, response.posts.error, response.posts.point);
                            setTimeout(importPosts, settings.timeDelay);
                        } else {
                            setTimeout(importComments, settings.timeDelay);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                        showTryImport(settings.processPosts);
                        autoRetry(settings.processPosts);
                    }
                });
            }

            function importComments() {
                $.ajax({
                    url: settings.urlRun,
                    type: 'POST',
                    data: {
                        action: 'le_cart_migration',
                        process: 'import',
                        type: 'comments'
                    },
                    dataType: 'json',
                    success: function(response, textStatus, jqXHR) {
                        if(!response){
                            showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                            showTryImport(settings.processComments);
                            autoRetry(settings.processComments);
                            return false;
                        }
                        if (response.msg !== '') {
                            showCslMsg(response.msg);
                        }
                        if (response.result === 'success') {
                            showProcessBar(settings.processComments, response.comments.real_total, response.comments.imported, response.comments.error, response.comments.point);
                            deleteLecmCookie();
                            $(settings.formImportSubmit).show();
                            $(settings.formImportSubmit).trigger('click');
                        } else if (response.result === 'error') {
                            showTryImport(settings.processComments);
                            autoRetry(settings.processComments);
                        } else if (response.result === 'process') {
                            showProcessBar(settings.processComments, response.comments.real_total, response.comments.imported, response.comments.error, response.comments.point);
                            setTimeout(importComments, settings.timeDelay);
                        } else {
                            $(settings.formImportSubmit).show();
                            deleteLecmCookie();
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        showCslMsg('<p class="error">' + settings.msgTryError + '</p>');
                        showTryImport(settings.processComments);
                        autoRetry(settings.processComments);
                    }
                });
            }

            function checkElementShow(elm){
                var check = $(elm).is(':visible');
                return check;
            }

            function triggerClick(elm){
                var par_elm = elm+' .try-import';
                var check_show = checkElementShow(par_elm);
                var button = $(par_elm).children('div');
                if(check_show){
                    button.trigger('click');
                }
            }

            function autoRetry(elm){
                if(settings.autoRetry > 0){
                    setTimeout(function(){triggerClick(elm)}, settings.autoRetry);
                }
            }

            return run();

            function clearData(){
                $(document).on('click', settings.clearCurrentData, function() {
                    $(settings.clearDataLoading).show();
                    $('#clear-data').hide();
                    $.ajax({
                        url: settings.urlRun,
                        type: 'POST',
                        data: {
                            action: 'le_cart_migration',
                            process: 'clearData'
                        },
                        dataType: 'json',
                        success: function(response, textStatus, jqXHR) {
                            $(settings.clearDataLoading).hide();
                            $('#clear-data').show();
                            if (response.result === 'success') {
                                alert('Data cleared succesfully.');
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            $(settings.clearDataLoading).hide();
                            $(settings.tryClearData).show();
                            alert('Data clearance incompleted. Please try again.');
                        }
                    });
                });
            }

            function run() {
                deleteLecmCookie();

                $(window).on('beforeunload', function() {
                    var check = checkLecmCookie();
                    if (check === true) {
                        return "Migration is in progress, leaving current page will stop it! Are you sure want to stop?";
                    }
                });

                clearData();

                $(document).on('click', settings.tryClearData, function() {
                    $(settings.tryClearData).hide();
                    clearData();
                });

                $(document).on('click', settings.formCartSubmit, function() {
                    $(settings.formResumeWrap).hide();
                    var cart_url = $('#cart_url').val();
                    if (cart_url == ""){
                        $('#error-url').show();
                        return false;
                    }
                    else {
                        $('#error-url').hide();
                    }
                    if (cart_url.indexOf('http://') == -1 && cart_url.indexOf('https://') == -1 && cart_url.indexOf('ftp://') == -1){
                        $('#error-http').show();
                        return false;
                    }
                    else {
                        $('#error-http').hide();
                    }
                    $(settings.formCartWrap).find('.lecm-error').hide();
                    $(settings.formCartLoading).show();
                    var data = $(settings.formCart).serialize();
                    $.ajax({
                        url: settings.urlRun,
                        type: 'post',
                        data: data,
                        dataType: 'json',
                        success: function(response, textStatus, jqXHR) {
                            $(settings.formCartLoading).hide();
                            if (response.result === 'success') {
                                enabledMenu(settings.menuConfig);
                                $(settings.formCartWrap).find('.lecm-ok').css({display: 'inline-block'});
                                $(settings.formCartWrap).hide();
                                $(settings.formConfigWrap).html(response.html);
                                $(settings.formConfigWrap).show();
                            } else if (response.result === 'warning') {
                                $(response.elm).parents('.form-group').find('.lecm-ok').hide();
                                if(response.msg !== ''){
                                    $(response.elm).html(response.msg);
                                }
                                $(response.elm).show();
                            } else {
                                alert(response.msg);
                            }
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            $(settings.formCartLoading).hide();
                            alert(settings.errorMsg);
                        }
                    });
                });

                $(document).on('click', '.entity-label', function() {
                    $(this).parent().children('input').trigger('click');
                });

                $(document).on('click', '#select-all', function() {
                    $('.lv0').find('input').prop('checked', $(this).prop('checked'));
                });

                $(document).on('click', '.lv2', function() {
                    var _this = $(this);
                    if (_this.prop('checked') === true) {
                        _this.parents('.lv0').find('.lv1').prop('checked', true);
                    }
                });

                $(document).on('click', '.lv1', function() {
                    var _this = $(this);
                    if (_this.prop('checked') === false) {
                        _this.parents('.lv0').find('.lv2').prop('checked', false);
                    }
                });

                $(document).on('click', settings.formConfigSubmit, function() {
                    if (checkSelectLangDuplicate() === true &&
                        validationEntitySelect() === true) {
                        $(settings.formConfigLoading).show();
                        var data = $(settings.formConfig).serialize();
                        $.ajax({
                            url: settings.urlRun,
                            type: 'post',
                            data: data,
                            dataType: 'json',
                            success: function(response, textStatus, jqXHR) {
                                $(settings.formConfigLoading).hide();
                                if (response.result === 'success') {
                                    enabledMenu(settings.menuConfirm);
                                    $(settings.formConfigWrap).hide();
                                    $(settings.formConfirmWrap).html(response.html);
                                    $(settings.formConfirmWrap).show();
                                } else {
                                    alert(response.msg);
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                $(settings.formConfigLoading).hide();
                                alert(settings.errorMsg);
                            }
                        });
                    } else {
                        alert('To proceed, please check and correct your configurations highlighted in red.');
                    }
                });

                $(document).on('click', settings.formConfigBack, function() {
                    disabledMenu(settings.menuConfig);
                    $(settings.formConfigWrap).hide();
                    $(settings.formCartWrap).show();
                    return false;
                });

                $(document).on('click', settings.formConfirmBack, function() {
                    disabledMenu(settings.menuConfirm);
                    $(settings.formConfirmWrap).hide();
                    $(settings.formConfigWrap).show();
                });

                $(document).on('click', settings.formConfirmSubmit, function() {
                    $(settings.formConfirmLoading).show();
                    var data = $(settings.formConfirm).serialize();
                    $.ajax({
                        url: settings.urlRun,
                        type: 'post',
                        data: data,
                        dataType: 'json',
                        success: function(response, textStatus, jqXHR) {
                            $(settings.formConfirmLoading).hide();
                            if (response.result === 'success') {
                                createLecmCookie(1);
                                $(settings.formConfirmWrap).hide();
                                $(settings.formImportWrap).html(response.html);
                                $(settings.formImportWrap).show();
                                setTimeout(clearShop, settings.timeDelay);
                            } else {
                                alert(response.msg);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            $(settings.formConfirmLoading).hide();
                            alert(settings.errorMsg);
                        }
                    });
                });

                $(document).on('click', settings.formResumeSubmit, function(){
                    $(settings.formResumeLoading).show();
                    var data = $(settings.formResume).serialize();
                    $.ajax({
                        url: settings.urlRun,
                        type: 'POST',
                        data: data,
                        dataType: 'json',
                        success: function(response, textStatus, jqXHR) {
                            $(settings.formResumeLoading).hide();
                            if(response.result === 'success'){
                                createLecmCookie(1);
                                $(settings.formImportWrap).html(response.html);
                                $(settings.formResumeWrap).hide();
                                $(settings.formCartWrap).hide();
                                $(settings.formImportWrap).show();
                                setTimeout(eval(settings.fnResume), settings.timeDelay);
                            } else {
                                alert(response.msg);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            $(settings.formResumeLoading).hide();
                            alert(settings.errorMsg);
                        }
                    });
                });

                $(document).on('click', settings.tryImportTaxes, function(){
                    hideTryImport(settings.processTaxes);
                    importTaxes();
                });

                $(document).on('click', settings.tryImportManufacturers, function(){
                    hideTryImport(settings.processManufacturers);
                    importManufacturers();
                });

                $(document).on('click', settings.tryImportCategories, function(){
                    hideTryImport(settings.processCategories);
                    importCategories();
                });

                $(document).on('click', settings.tryImportProducts, function(){
                    hideTryImport(settings.processProducts);
                    importProducts();
                });

                $(document).on('click', settings.tryImportCustomers, function(){
                    hideTryImport(settings.processCustomers);
                    importCustomers();
                });

                $(document).on('click', settings.tryImportOrders, function(){
                    hideTryImport(settings.processOrders);
                    importOrders();
                });

                $(document).on('click', settings.tryImportReviews, function(){
                    hideTryImport(settings.processReviews);
                    importReviews();
                });

                $(document).on('click', settings.tryImportPages, function(){
                    hideTryImport(settings.processPages);
                    importPages();
                });

                $(document).on('click', settings.tryImportPostCat, function(){
                    hideTryImport(settings.processPostCat);
                    importPostCat();
                });

                $(document).on('click', settings.tryImportPosts, function(){
                    hideTryImport(settings.processPosts);
                    importPosts();
                });

                $(document).on('click', settings.tryImportComments, function(){
                    hideTryImport(settings.processComments);
                    importComments();
                });

                $(document).on('click', settings.tryClearShop, function(){
                    $(this).hide();
                    setTimeout(clearShop, settings.timeDelay);
                });

                $(document).on('click', '#choose-seo', function(){
                    $('#seo_plugin').slideToggle();
                });

                $(document).on('click', settings.formImportSubmit, function(){
                    var _this = $(this);
                    _this.hide();
                    $(settings.formImportLoading).show();
                    $.ajax({
                        url: settings.urlRun,
                        type: 'POST',
                        data: {
                            action: 'le_cart_migration',
                            process: 'finish'
                        },
                        dataType: 'json',
                        success: function(response, textStatus, jqXHR) {
                            $(settings.formImportLoading).hide();
                            showCslMsg(response.msg);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            $(settings.formImportLoading).hide();
                            showCslMsg('<p class="error">Request timeout or server isn\'t responding, please reindex and clear cache manually.</p>');
                        }
                    });
                });

            }
        }
    });
})(jle);