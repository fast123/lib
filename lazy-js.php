<?if (! defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * @global CMain $APPLICATION
 */

    \FourPx\Asset::addCss(ASSETS_URL . '/libs/slick-1.8.1/slick.css');
    \FourPx\Asset::addCss(ASSETS_URL . '/libs/slick-1.8.1/slick-theme.css');
    \FourPx\Asset::addCss(ASSETS_URL . '/libs/select2/select2.min.css');
    \FourPx\Asset::addCss(ASSETS_URL . '/libs/Vue/vue2-datepicker.css');
    \FourPx\Asset::addCss(ASSETS_URL . '/libs/Vue/vue-select.css');
    \FourPx\Asset::addCss(ASSETS_URL . '/libs/magnific/magnific-popup.min.css');
    \FourPx\Asset::registerCss();
?>

    <script>
        <?# ключ защищающий от робота?>
        BX.message({
            WEB_FORM_KEY: '<?= WEB_FORM_KEY?>'
        });

    </script>

    <script>
        let isLoadSources = false;

        window.addEventListener("mousemove", userEventsInit)
        window.addEventListener("touchstart", userEventsInit)
        window.addEventListener('scroll', userEventsInit);

        function userEventsInit() {
            window.removeEventListener("mousemove", userEventsInit);
            window.removeEventListener("touchstart", userEventsInit);
            window.removeEventListener('scroll', userEventsInit);

            loadStyles("https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css");


            loadScript("<?= ASSETS_URL?>/libs/jquery-3.5.1.min.js")
                .then(script => {
                    document.dispatchEvent(new Event('jquery.loaded'));
                })
                .then(script => loadScript("<?= ASSETS_URL?>/libs/jquery.inputmask.min.js"))
                .then(script => loadScript("<?= ASSETS_URL?>/libs/slick-1.8.1/slick.min.js"))
                .then(script => loadScript("<?= ASSETS_URL?>/libs/select2/select2.min.js"))
                .then(script => loadScript("<?= ASSETS_URL?>/libs/Vue/vue.min.js"))
                .then(script => loadScript("<?= ASSETS_URL?>/libs/Vue/vue2-datepicker.min.js"))
                .then(script => loadScript("<?= ASSETS_URL?>/libs/Vue/vue2-datepicker-ru.js"))
                .then(script => loadScript("<?= ASSETS_URL?>/libs/Vue/vue-select.js"))
                .then(script => loadScript("<?= ASSETS_URL?>/libs/Vue/v-mask.min.js"))
                .then(script => loadScript("<?= ASSETS_URL?>/libs/axios.min.js"))
                .then(script => {
                    document.dispatchEvent(new Event('vue.loaded'));
                })
                .then(script => loadScript("<?= ASSETS_URL?>/libs/magnific/jquery.magnific-popup.min.js"))
                .then(script => loadScript("https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"))
                .then(script => loadScript("https://api-maps.yandex.ru/2.1/?apikey=28517946-dbf1-42a5-9b36-08632da36a93&lang=ru_RU&"))
                .then(script => loadScript("<?= ASSETS_URL?>/js/main.js?<?= filemtime(ASSETS_FOLDER . '/js/main.js')?>"))
                .then(script => isLoadSources = true )
        }

        function loadStyles(href) {
            return new Promise(function(resolve, reject) {
                let link = document.createElement('link');
                link.href = href;
                link.rel = "stylesheet"
                link.type = "text/css"
                link.onload = () => resolve(link);
                link.onerror = () => reject(new Error(`Ошибка загрузки скрипта ${src}`));

                document.head.append(link);
            });
        }

        function loadScript(src) {
            return new Promise(function(resolve, reject) {
                let script = document.createElement('script');
                script.src = src;

                script.onload = () => resolve(script);
                script.onerror = () => reject(new Error(`Ошибка загрузки скрипта ${src}`));

                document.head.append(script);
            });
        }


        headerInit()

        function headerInit() {

            const burger = document.querySelector(".js-header-burger");
            const headerModal = document.querySelector(".js-header-modal");


            burger.addEventListener("click", openModal)

            function openModal() {
                if (isLoadSources) return
                headerModal.style.display = "block"
                document.body.style.overflow = "hidden"

                return burger.removeEventListener("click", openModal)
            }
        }
    </script>
</body>
</html>
