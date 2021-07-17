<div class="article-content"><h2 role="button">Требования / рекомендации к вёрстке</h2><p><a href="https://docs.google.com/document/d/1RFG0cfWyS_NgPZdZ13L5j-bZp8V_iKEGMRhk-ukl9pQ/edit" target="_blank">https://docs.google.com/document/d/1RFG0cfWyS_NgPZdZ13L5j-bZp8V_iKEGMRhk-ukl9pQ/edit</a></p><h2 role="button">Google Page Speed оптимизация</h2><h3 role="button">Добавление изображений</h3><p>Изображения необходимо добавлять следующим образом, при это все атрибуты (width, height, alt, loading, ...) нужно указывать обязательно:</p><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs xml" spellcheck="false"><span class="hljs-tag">&lt;<span class="hljs-name">picture</span>&gt;</span>
  <span class="hljs-tag">&lt;<span class="hljs-name">source</span> <span class="hljs-attr">type</span>=<span class="hljs-string">"image/webp"</span> <span class="hljs-attr">srcset</span>=<span class="hljs-string">"aaa.webp"</span>&gt;</span>
  <span class="hljs-tag">&lt;<span class="hljs-name">img</span> <span class="hljs-attr">src</span>=<span class="hljs-string">"aaa.jpg"</span> <span class="hljs-attr">width</span>=<span class="hljs-string">"200"</span> <span class="hljs-attr">height</span>=<span class="hljs-string">"100"</span> <span class="hljs-attr">alt</span>=<span class="hljs-string">""</span> <span class="hljs-attr">loading</span>=<span class="hljs-string">"lazy"</span>&gt;</span>
<span class="hljs-tag">&lt;/<span class="hljs-name">picture</span>&gt;</span>
</pre><p>Пример более полноценного варианта под разные разрешения и retina:</p><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs xml" spellcheck="false"><span class="hljs-tag">&lt;<span class="hljs-name">picture</span>&gt;</span>
    <span class="hljs-tag">&lt;<span class="hljs-name">source</span> <span class="hljs-attr">media</span>=<span class="hljs-string">"(max-width: 640px)"</span> <span class="hljs-attr">type</span>=<span class="hljs-string">"image/webp"</span> <span class="hljs-attr">srcset</span>=<span class="hljs-string">"aaa.webp 1x, aaa2x.webp 2x"</span>&gt;</span>
    <span class="hljs-tag">&lt;<span class="hljs-name">source</span> <span class="hljs-attr">media</span>=<span class="hljs-string">"(max-width: 640px)"</span> <span class="hljs-attr">srcset</span>=<span class="hljs-string">"aaa.jpg 1x, aaa2x.jpg 2x"</span>&gt;</span>
    <span class="hljs-tag">&lt;<span class="hljs-name">source</span> <span class="hljs-attr">type</span>=<span class="hljs-string">"image/webp"</span> <span class="hljs-attr">srcset</span>=<span class="hljs-string">"aaa.webp 1x, aaa2x.webp 2x"</span>&gt;</span>
    <span class="hljs-tag">&lt;<span class="hljs-name">img</span> <span class="hljs-attr">src</span>=<span class="hljs-string">"aaa.jpg"</span>
        <span class="hljs-attr">srcset</span>=<span class="hljs-string">"/images/aaa2x.jpg 2x"</span>
        <span class="hljs-attr">width</span>=<span class="hljs-string">"200"</span>
        <span class="hljs-attr">height</span>=<span class="hljs-string">"100"</span>
        <span class="hljs-attr">alt</span>=<span class="hljs-string">""</span>
        <span class="hljs-attr">loading</span>=<span class="hljs-string">"lazy"</span>&gt;</span>
<span class="hljs-tag">&lt;/<span class="hljs-name">picture</span>&gt;</span>

</pre><p>Атрибуты <code role="button">width</code>, <code role="button">height</code>, при условии что в стилях указано какие ширина и высота должны быть у изображения, создадут прямоугольник нужных пропорций ещё до загрузки изображения с сервера. Тем самым удастся избавиться от смещения макета.</p><p>Атрибут <code role="button">loading="lazy"</code>&nbsp;понимают все современные браузеры. И они сами осуществляют т.н. ленивую загрузку. Т.о. не нужно использовать всякие библиотеки ленивой загрузки и делать прямоугольники малых размеров.</p><p>Стиль для картинки может выглядеть, к примеру, так:</p><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs xml" spellcheck="false"><span class="hljs-tag">&lt;<span class="hljs-name">style</span>&gt;</span><span class="css">
    <span class="hljs-selector-tag">img</span> {<span class="hljs-attribute">max-width</span>: <span class="hljs-number">100%</span>; <span class="hljs-attribute">height</span>: auto;}
</span><span class="hljs-tag">&lt;/<span class="hljs-name">style</span>&gt;</span>
</pre><p>Для получения <code role="button">webp</code> можно воспользоваться <a href="https://gitlab.4px.ru/4px/wiki-bitrix-storage/-/blob/master/docs/webp/webp.php" target="_blank">этим решением</a>.</p><h2 role="button">Добавление фоновых изображений WebP</h2><p>Для добавления фоновых изображений WebP можно определить на стороне&nbsp; сервера поддерживается ли этот тип изображений:</p><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs php" spellcheck="false"><span class="hljs-keyword">if</span> (strpos($_SERVER[<span class="hljs-string">'HTTP_ACCEPT'</span>], <span class="hljs-string">'image/webp'</span>)) {
    define(<span class="hljs-string">'WEBP_SUPPORT'</span>, <span class="hljs-keyword">true</span>);
} <span class="hljs-keyword">else</span> {
    define(<span class="hljs-string">'WEBP_SUPPORT'</span>, <span class="hljs-keyword">false</span>);
}
</pre><p>И если WebP поддерживается, то добавить класс тегу <code role="button">&lt;body&gt;</code>:</p><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs xml" spellcheck="false"><span class="hljs-tag">&lt;<span class="hljs-name">body</span> <span class="hljs-attr">class</span>=<span class="hljs-string">"webp-on"</span>&gt;</span>
  ...
<span class="hljs-tag">&lt;/<span class="hljs-name">body</span>&gt;</span>
</pre><p>Соответственно, в стилях переопределить изображения для соответствующих элементов:</p><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs php" spellcheck="false"><span class="hljs-comment">/* обычные стили */</span>
.block1 {
  ...
  background-image: url(<span class="hljs-string">'picutre1.jpg'</span>);
  ...
}

...

<span class="hljs-comment">/* переопределение изображения когда WebP поддерживается */</span>
.webp-on .block1 {
background-image: url(<span class="hljs-string">'picture1.webp'</span>);
}
</pre><h3 role="button">Подключение шрифтов</h3><p>Подключать имеет смысл только шрифты форматов <code role="button">woff</code> и <code role="button">woff2.</code>&nbsp;Для старых браузеров не поддерживающих woff и woff2 нужно подобрать безопасные веб-шрифты, с которыми бы вёрстка не ехала.</p><p>При использовании сторонних шрифтов Google Insight просит их подключить в блоке head. Это делается следующим образом. Все атрибуты обязательны:</p><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs php" spellcheck="false">&lt;link rel=<span class="hljs-string">"preload"</span> <span class="hljs-keyword">as</span>=<span class="hljs-string">"font"</span> href=<span class="hljs-string">"/fonts/roboto.woff2"</span> crossorigin=<span class="hljs-string">"anonymous"</span>&gt;
</pre><h3 role="button">Подключение скриптов</h3><p>Google Insight засчитывает время на загрузку и отработку скриптов, которые подключаются сразу, работают по document.ready, window.load, setTimeout, ... Но он не берёт в расчёт те скрипты, которые начинают работать по событиям взаимодействия с сайтом пользователя. В основном это "scroll" и "mousemove".</p><p>Поэтому имеет смысл подключать различные скрипты только в тех случаях, когда пользователь начинает взаимодействие с сайтом. Например, карта, слайдер и прочее, не попадающее на первый экран. Даже библиотеку jQuery или какие-то мессенджеры или аналитики может иметь смысл подключать когда пользователь начал взаимодействие с сайтом.</p><p>Пример того как это можно выглядеть:</p><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs javascript" spellcheck="false"><span class="hljs-built_in">window</span>.addEventListener(<span class="hljs-string">'scroll'</span>, userEventsInit);
<span class="hljs-built_in">window</span>.addEventListener(<span class="hljs-string">'mousemove'</span>, userEventsInit);

<span class="hljs-function"><span class="hljs-keyword">function</span> <span class="hljs-title">userEventsInit</span>(<span class="hljs-params"></span>) </span>{
<span class="hljs-built_in">window</span>.removeEventListener(<span class="hljs-string">'scroll'</span>, userEventsInit);
<span class="hljs-built_in">window</span>.removeEventListener(<span class="hljs-string">'mousemove'</span>, userEventsInit);

    <span class="hljs-comment">/* к примеру, если этто уместно, загрузка метрики */</span>
    <span class="hljs-keyword">var</span> metrika = <span class="hljs-built_in">document</span>.createElement(<span class="hljs-string">'script'</span>);
    metrika.innerHTML = <span class="hljs-string">'\
    (function(m,e,t,r,i,k,a) ....\
    '</span>;
    <span class="hljs-built_in">document</span>.body.appendChild(metrika);
    <span class="hljs-comment">/* к примеру, если этто уместно, загрузка метрики */</span>


    <span class="hljs-comment">/* загрузка jQuery */</span>
    loadJsScript(assetsPath + <span class="hljs-string">'/libs/jquery-3.5.1.min.js'</span>, <span class="hljs-function"><span class="hljs-keyword">function</span>(<span class="hljs-params"></span>) </span>{
        <span class="hljs-keyword">let</span> $<span class="hljs-built_in">document</span> = $(<span class="hljs-built_in">document</span>);
        <span class="hljs-keyword">let</span> $<span class="hljs-built_in">window</span> = $(<span class="hljs-built_in">window</span>);

        <span class="hljs-comment">/* после как загрузился jQuery */</span>


        <span class="hljs-comment">/* Инициализация слайдеров &gt; */</span>
        ;(<span class="hljs-function"><span class="hljs-keyword">function</span>(<span class="hljs-params"></span>) </span>{
            <span class="hljs-keyword">let</span> $sliders = $(<span class="hljs-string">'.slider__inner'</span>);

            <span class="hljs-comment">/* если на странице есть хоть один слайдер - загрузить стили, скрипты для них */</span>
            <span class="hljs-keyword">if</span> ($sliders.length &gt; <span class="hljs-number">0</span>) {
                loadStyle(assetsPath + <span class="hljs-string">'/libs/slick-1.8.1/slick.css'</span>);
                loadStyle(assetsPath + <span class="hljs-string">'/libs/slick-1.8.1/slick-theme.css'</span>);

                loadJsScript(assetsPath + <span class="hljs-string">'/libs/slick-1.8.1/slick.min.js'</span>, <span class="hljs-function"><span class="hljs-keyword">function</span> (<span class="hljs-params"></span>) </span>{

                    <span class="hljs-comment">/* после загрузки библиотеки - загрузка скрипта инициализации слайдера */</span>
                    loadJsScript(assetsPath + <span class="hljs-string">'/js/slider.js'</span>);
                });
            }
        })();
        <span class="hljs-comment">/* Инициализация слайдеров &lt; */</span>

        <span class="hljs-comment">/* загрузка карты Яндекс &gt; */</span>
        ;(<span class="hljs-function"><span class="hljs-keyword">function</span>(<span class="hljs-params"></span>) </span>{
            <span class="hljs-keyword">let</span> $maps = $(<span class="hljs-string">'.contacts__map'</span>);

            <span class="hljs-comment">/* если на странице есть хоть одна карта - загрузить скрипт карт - инициализоровать карты */</span>
            <span class="hljs-keyword">if</span> ($maps.length &gt; <span class="hljs-number">0</span>) {
                loadJsScript(<span class="hljs-string">'https://api-maps.yandex.ru/2.1/?apikey=XXXXXX-YYYYYY-ZZZZZZ&amp;lang=ru_RU'</span>, <span class="hljs-function"><span class="hljs-keyword">function</span>(<span class="hljs-params"></span>) </span>{
                    ymaps.ready(<span class="hljs-function"><span class="hljs-keyword">function</span>(<span class="hljs-params"></span>) </span>{
                        $maps.each(<span class="hljs-function"><span class="hljs-keyword">function</span>(<span class="hljs-params"></span>) </span>{
                            <span class="hljs-keyword">let</span> map = <span class="hljs-keyword">this</span>;

                            <span class="hljs-keyword">new</span> ymaps.Map(map, {
                                <span class="hljs-attr">center</span>: [<span class="hljs-number">50</span>, <span class="hljs-number">50</span>],
                                <span class="hljs-attr">zoom</span>: <span class="hljs-number">7</span>
                            });
                        });
                    });
                });
            }
        })();
        <span class="hljs-comment">/* загрузка карты Яндекс &lt; */</span>
    }

}


<span class="hljs-comment">/* загрузка стилей */</span>
<span class="hljs-function"><span class="hljs-keyword">function</span> <span class="hljs-title">loadStyle</span>(<span class="hljs-params">url</span>) </span>{
<span class="hljs-keyword">let</span> link = <span class="hljs-built_in">document</span>.createElement(<span class="hljs-string">'link'</span>);
link.href = url;
link.rel = <span class="hljs-string">'stylesheet'</span>;
<span class="hljs-built_in">document</span>.body.appendChild(link);
}

<span class="hljs-comment">/* загрузка скрипта с callback по окончании загрузки скрипта */</span>
<span class="hljs-function"><span class="hljs-keyword">function</span> <span class="hljs-title">loadJsScript</span>(<span class="hljs-params">url, callback</span>) </span>{
<span class="hljs-keyword">let</span> jsScript = <span class="hljs-built_in">document</span>.createElement(<span class="hljs-string">'script'</span>);
jsScript.src = url;
jsScript.type = <span class="hljs-string">'text/javascript'</span>;

    jsScript.onload = callback;

    <span class="hljs-built_in">document</span>.body.appendChild(jsScript);
}
</pre><p><br></p></div>