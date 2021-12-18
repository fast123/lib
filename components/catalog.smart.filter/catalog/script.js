var SmartFilterUrl = {
    construct(url, blockCatalog){
        this.url = url;
        this.blockCatalog = blockCatalog;
    },
    changeHistory:  function (urlParams, url = this.url) {
        let page = url !== '' ? url : window.location.pathname;
        page = urlParams == '' ? page : page + '?';
        history.pushState({ param: 'Value' }, '', page + urlParams);
    },
    /*
        возвращает сртроку get параметров
        @param dataForm масив обьъектов полученный через serializeArray
        return string
     */
    getParams: function (dataForm) {
        var urlParams = '';

        if(dataForm){
            $(dataForm).each(function(i, elem){
                if(elem.name=='submit') return false;
                urlParams+=elem.name + '=' + elem.value + '&';
            });
        }
        return urlParams
    },
    changeUrl: function (dataForm) {
        this.blockCatalog.addClass('load');
        var urlParams = this.getParams(dataForm);
        this.changeHistory(urlParams);
        return urlParams
    }

};

/*
    Класс реализующий фильтрацию catalog.smart.filte
 */
var SmartFilter = {

    /*
        Отправка данных для фильтрации
        @param dataForm array Данные формы свормированые через serializeArray
        @return предполагается что верстку фильтра и каталога
     */
    sendAjax: function(url, dataForm, callback, method = 'POST'){
        callback = callback || function () {
        };
        $.ajax({
            url: url,
            type: method,
            data: dataForm,
            success: function (data) {
                callback(data)
            },
            error: function (data) {
                callback({
                    type: 'error',
                    class: 'danger',
                    text: data.responseText
                })
            }
        })
    },

    /*
        Функция заменяет верстку и запускает инициализации(например инициализация select)
     */
    afterSendAjax: function (data, blockCatalog) {
        var html = $(data).html();
       /* var arHtml = {
            filter:$(data).filter('.js-filter-wrapper').html(),
            catalog:$(data).filter('.js-catalog-container').html(),
        };*/


        /*if (arHtml.filter) {
            $('.js-filter-wrapper').empty().html(arHtml.filter)
        }
        if (arHtml.catalog) {
            $('.js-catalog-container').empty().html(arHtml.catalog)
        }*/
        $('.catalog__wrap').html(html);
        blockCatalog.removeClass('load');

    },

    /*
        Измеение и отправка данных, индивидуально для каждого сайта
     */
    submitForm: function(){
        var dataForm = $('.js-smartfilter').serializeArray();
        var dataDopForm = $('.js-catalog-control').serializeArray();//доп данные находящмеся в другой форме
        var action = $('.js-smartfilter').attr('action');
        var blockCatalog = $('.catalog__wrap');
        var arAction = action.split('?');
        var dataFilter = [{
            'name': 'submit',
            'value': 'Y'
        }];

        if(dataDopForm.length>0){
            $(dataDopForm).each(function (i, elem){
                dataForm.push(elem);
            });
        }

        if(arAction[1]){
            var arGetParam = arAction[1].split('&');
            if(arGetParam){
                $(arGetParam).each(function(i, elem){
                    var arItem = elem.split('=');
                    var item = {};
                    item.name = arItem[0];
                    item.value = arItem[1];

                    if(item.name == 'set_filter' || item.name == 'PAGE_1'){
                        dataForm.push(item);
                    }
                });

            }
        }

        SmartFilterUrl.construct(arAction[0], blockCatalog);
        $strGetParams = SmartFilterUrl.changeUrl(dataForm);

        SmartFilter.sendAjax(arAction[0]+'?'+$strGetParams, dataFilter, function(data){
            SmartFilter.afterSendAjax(data, blockCatalog);
            SmartFilter.catalogItemInit();
        });
    },

    /*
    Измеение и отправка данных, индивидуально для каждого сайта
    */
    resetForm: function () {
        var dataForm = [];
        var blockCatalog = $('.catalog__wrap');
        var action = $('.js-smartfilter').attr('action');
        var arAction = action.split('?');
        var dataFilter = [{
            'name': 'submit',
            'value': 'Y'
        }];

        SmartFilterUrl.construct(arAction[0], blockCatalog);
        var $strGetParams = SmartFilterUrl.changeUrl(dataForm);

        SmartFilter.sendAjax(arAction[0]+'?'+$strGetParams, dataFilter, function(data){
            SmartFilter.afterSendAjax(data, blockCatalog);
            SmartFilter.catalogItemInit();
        });

    },

    catalogItemInit: function(){
        $(".js-catalog-slider").slick({
            slidesToShow: 1,
            dots: true,
            arrows: false,
            mobileFirst: false,
            infinite: true,
            slidesToScroll: 1,
            autoplay: false,
        });

    },
};


$(document).ready(function (){
    $(document).on('change', '.js-smartfilter', function(e){
        e.preventDefault();
        if(document.documentElement.clientWidth<=768){
            return false;
        }
        SmartFilter.submitForm();
    });

    $(document).on('submit', '.js-smartfilter', function(e){
        e.preventDefault();
        $('.js-filter-container').removeClass('active');
        SmartFilter.submitForm();
    });

    $(document).on('change', '.js-catalog-control', function(e){
        e.preventDefault();
        SmartFilter.submitForm();
    });

    $(document).on('click', '.js-clear-filter', function (e){
        e.preventDefault();
        $('.js-filter-container').removeClass('active');
        SmartFilter.resetForm();
    });


    $(document).on('click', '.js-sort-item', function(){
        var val = $(this).attr('data-val');
        var text = $(this).text();
        $('.js-sort-cintro').val(val);
        $('.js-sort').find('.catalog__sort-label').text(text);
        $('.js-catalog-control').change();
    });

    $(document).on('change', '.js-avaliable-input', function(){
        var val = $(this).val();
        $('.js-avaliable-input').prop('checked', false);
        $(this).prop('checked', true);
        $('.js-avaliable-cintro').val(val);
        $('.js-catalog-control').change();
    });


});