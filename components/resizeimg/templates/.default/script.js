$(document).ready(function(){
    var imageSection, arFiles = {};
    var coordinate_selection = {
        'width': 300,
        'height': 300,
        'x1': 0,
        'y1':0
    };

    $(document).on('click', '.js-submit_selection', function () {
        coordinate_selection.rotate = imageSection.getData().rotate;
        coordinate_selection.ajax = 'Y';

        if(coordinate_selection.x1<0) coordinate_selection.x1 = 0;
        if(coordinate_selection.y1<0) coordinate_selection.y1 = 0;

        var mainData = JSON.stringify(coordinate_selection),
            action = $(this).attr('data-action'),
            idCompany = $(this).attr('data-id'),
            url;

        if(action == 'company'){
            url = '/response/ajax/resizeimg_company.php';
            if(idCompany){
                url_reload = '/profile/company/?id='+idCompany;
            }
            else{
                url_reload = '/profile/company/?';
            }

        }
        else{
            url = '/response/ajax/resizeimg.php';
            url_reload = '/profile/';
        }

        if( typeof arFiles != 'undefined' && Object.keys(arFiles).length >0){
            var formData = new FormData();
            formData.append( 'main', mainData );

            // заполняем объект данных файлами в подходящем для отправки формате
            for (var id in arFiles) {
                formData.append(id, arFiles[id]);
            }

            preload('show');
            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                processData: false,
                contentType: false,
                success: function(data){
                    let result = JSON.parse(data);
                    if(result['TYPE']=='ERROR'){
                        preload('hide');
                        showResult('#popup-error','Ошибка сохранения');
                        console.log(result['VALUE']);
                    }
                    if(result['TYPE']=='SUCCESS'){
                        preload('hide');
                        showResult('#popup-success','Изменения сохранены');
                        if(result.VALUE == undefined){
                            location.href = url_reload;
                        }else{
                            location.href = url_reload+'&img='+result.VALUE;
                        }
                    }
                }
            });
        }

    });


    $('#filePicture').on('change', function () {
        var files = this.files;
        for (var i = 0; i < files.length; i++) {
            preview(files[i]);
        }

        this.value = '';

        $('.cart-tab').show();

        setTimeout(function(){
            imageSection = new Cropper( $('.resize-img')[0], {
                dragMode: 'none',
                zoomable: false,
                /*cropBoxResizable: false,*/
                aspectRatio: 1,
                autoCropArea:0.1,
                minCropBoxWidth: 300,
                minCropBoxHeight: 300,
                maxCropBoxWidth: 300,
                maxCropBoxHeight: 300,
                rotatable: true,
                crop(event) {
                    coordinate_selection.x1 = event.detail.x;
                    coordinate_selection.y1 = event.detail.y;
                    coordinate_selection.width = event.detail.width;
                    coordinate_selection.height = event.detail.height;
                },
            });
        }, 200);


    });

    // Создание превью
    function preview(file) {
        var reader = new FileReader();
        reader.addEventListener('load', function(e) {

            var img = document.createElement('img');

            img.setAttribute('class', 'resize-img');
            img.setAttribute('src', e.target.result);
            $('.cart-tab').attr('data-id', file.name);

            $('.cardPact-box').html(img);

            arFiles[file.name] = file;

        });
        reader.readAsDataURL(file);
    }


    //добавление изображения
    $(document).on('click', '.cardPact-box-edit', function( event ){
        $('#filePicture').click();
    });

    //удаление изображения
    $(document).on('click', '.js-rem_img',  function(){
        var div = '<div class="cardPact-box-edit">' +
                        '<div id="cardPact-box-edit-add_img">' +
                            '<span>+</span>' +
                        '</div>' +
                    '</div>';
        var id = $('.cart-tab').attr('data-id');

        //imageSection.cancelSelection();
        $('.cardPact-box').html(div);
        //$('.cart-tab').hide();
        $('.cart-tab').attr('data-id', '');

        delete arFiles[id];
    });

    //пововрот 90 в лево
    $(document).on('click', '.js-rotate__img_left', function(){
        imageSection.rotate(-90);
    });

    //пововрот 90 в вправо
    $(document).on('click', '.js-rotate__img_right', function(){
        imageSection.rotate(90);
    });
});