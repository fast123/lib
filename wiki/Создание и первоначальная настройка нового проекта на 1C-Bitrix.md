<div class="article-content"><p>Чек-лист по созданию нового проекта (разворачивать можно как на сервере так и на компьютере разработчика:</p><ol><li>Распаковать дистрибутив соответствующей редакции (Старт, Стандрат, ...) в каталог проекта (<a href="https://www.1c-bitrix.ru/download/cms.php#tab-section-3" target="_blank">скачать</a> дистрибутив)</li><li>Открыть сайт в браузере и начать установку продукта</li><li class="ql-indent-1">Указать кодировку сайта "utf-8"</li><li class="ql-indent-1">На шаге "Создание базы данных" выбрать тип таблиц "Innodb".</li><li class="ql-indent-1">На шаге "Выберите решение для установки" нужно выбрать "Загрузить из Маркетлейс" и из предложенных вариантов в следующем окне выбрать "Чистая установка "1С-Битрикс". В настройке решения необходимо ввести корректные данные по проекту и указать необходимые для проекта модули (лучше установить все модули, если нет понимания зачем они). Завершить установку решения.</li><li>Создать в корне сайта каталог <code role="button">/local/templates/</code>&nbsp;и переместить в него только что созданный шаблон из каталога <code role="button">/bitrix/templates/</code>.</li><li>Удалить всякие мусорные файлы из корня сайта (DS_Store, favicon.ico)</li><li>Добавить в файл <code role="button">.htaccess</code> стандартные правила для SEO хотя бы в закомментированном виде (см. листинг 1)</li><li>Настроить "Главный модуль":</li><li class="ql-indent-1">На вкладке "Авторизация" включить использование CAPTCHA везде где есть; снять галку с "Позволять ли пользователям регистрироваться самостоятельно?" если на сайте нет регистрации и авторизации пользователей; активировать пункты "Запрашивать подтверждение регистрации по email" и "Проверять email на уникальность при регистрации". Остальные параметры можно оставить как есть.</li><li class="ql-indent-1">На вкладке "Журнал событий" активировать все чекбоксы и поставить&nbsp; <code role="button">92</code>&nbsp; в поле "Сколько дней хранить события".</li><li class="ql-indent-1">На вкладке "Почта и СМС" в поле "Email администратора сайта (...)" вписать соответствующий email.</li><li>Добавить журналирование ошибок в файле <code role="button">/bitrix/.settings.php</code>&nbsp;(см. листинг 2)</li><li>Подготовить файлы для git:</li><li class="ql-indent-1">Создать копию файла <code role="button">.htaccess</code> с именем <code role="button">.htaccess.example</code></li><li class="ql-indent-1">Создать копию файла <code role="button">/bitrix/.settings.php</code> с именем <code role="button">/bitrix/.settings.php.example</code>&nbsp;и удалить в ней пароль подключения к БД</li><li class="ql-indent-1">Создать копию файла <code role="button">/bitrix/php_interface/dbconn.php</code> с именем <code role="button">/bitrix/php_interface/dbconn.php.example</code>&nbsp;и удалить в ней пароль подключения к БД</li><li class="ql-indent-1">Создать копию файла&nbsp;<code role="button">/bitrix/php_interface/after_connect.php</code>&nbsp;с именем&nbsp;<code role="button">/bitrix/php_interface/after_connect.php.example</code></li><li class="ql-indent-1">Создать копию файла&nbsp;<code role="button">/bitrix/php_interface/after_connect_d7.php</code>&nbsp;с именем&nbsp;<code role="button">/bitrix/php_interface/after_connect_d7.php.example</code></li><li class="ql-indent-1">В корне сайта создать файл&nbsp;<code role="button">.gitignore</code> (см. листинг 3)</li><li class="ql-indent-1">В каталоге <code role="button">/bitrix/</code>&nbsp;создать файл <code role="button">.gitignore</code> (см. листинг 4)</li><li class="ql-indent-1">В корне сайта создать файл robots.txt (см. листинг 7)</li><li>Файлы из корня сайта добавить в git и отправить их в ветку <code role="button">master</code>&nbsp;удалённого репозитория (см. листинг 5)</li><li>Файлы из папки /bitrix/&nbsp;добавить в git и отправить их в ветку <code role="button">bitrix-core</code> удалённого репозитория (см. листинг 6)</li><li>Защитить ветку <code role="button">bitrix-core</code>: в проекте gitlab в меню <code role="button">Seittings -&gt; Repository</code> в разделе <code role="button">Protected Branches</code>&nbsp;выбрать ветку <code role="button">bitrix-core</code> и проставить разрешения для <code role="button">Maintainers</code>, нажать <code role="button">Protect</code>.</li></ol><p>На этом создание проекта закончено. Можно переходить к развёртыванию: <a href="https://wiki.4px.tech/article/bitrix-deploy-existing-project" target="_blank">статья</a>.</p><p><br></p><p><br></p><h2 role="button">Листинг 1. Пример файла .htaccess</h2><p>(на примере сайта xyz.ru. "xyz" нужно поменять на домен своего сайта)</p><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs php" spellcheck="false">Options -Indexes 
ErrorDocument <span class="hljs-number">404</span> /<span class="hljs-number">404.</span>php

&lt;IfModule mod_php5.c&gt;
php_flag session.use_trans_sid off
&lt;/IfModule&gt;

&lt;IfModule mod_php7.c&gt;
php_flag session.use_trans_sid off
&lt;/IfModule&gt;

&lt;IfModule mod_rewrite.c&gt;
Options +FollowSymLinks
RewriteEngine On

<span class="hljs-comment"># http -&gt; https</span>
<span class="hljs-comment"># RewriteCond %{HTTP:X-Forwarded-Proto} !https</span>
<span class="hljs-comment"># RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=302]</span>

<span class="hljs-comment"># www -&gt;</span>
<span class="hljs-comment"># RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]</span>
<span class="hljs-comment"># RewriteRule ^(.*)$ http://%1/$1 [R=301,L]</span>

<span class="hljs-comment"># добавить /</span>
RewriteCond %{REQUEST_URI} /+[^\.]+$
RewriteRule ^(.+[^/])$ %{REQUEST_URI}/ [R=<span class="hljs-number">301</span>,L]

<span class="hljs-comment"># //// -&gt; /</span>
RewriteCond %{THE_REQUEST} <span class="hljs-comment">//</span>
RewriteRule .* /$<span class="hljs-number">0</span> [R=<span class="hljs-number">301</span>,L]

<span class="hljs-comment"># /index.php -&gt; /</span>
RewriteCond %{THE_REQUEST} ^.*/index.php
RewriteRule ^(.*)index.php$ http:<span class="hljs-comment">//%{HTTP_HOST}/$1 [R=301,L]</span>

RewriteCond %{REQUEST_FILENAME} -f [<span class="hljs-keyword">OR</span>]
RewriteCond %{REQUEST_FILENAME} -l [<span class="hljs-keyword">OR</span>]
RewriteCond %{REQUEST_FILENAME} -d
RewriteCond %{REQUEST_FILENAME} [\xC2-\xDF][\x80-\xBF] [<span class="hljs-keyword">OR</span>]
RewriteCond %{REQUEST_FILENAME} \xE0[\xA0-\xBF][\x80-\xBF] [<span class="hljs-keyword">OR</span>]
RewriteCond %{REQUEST_FILENAME} [\xE1-\xEC\xEE\xEF][\x80-\xBF]{<span class="hljs-number">2</span>} [<span class="hljs-keyword">OR</span>]
RewriteCond %{REQUEST_FILENAME} \xED[\x80-\x9F][\x80-\xBF] [<span class="hljs-keyword">OR</span>]
RewriteCond %{REQUEST_FILENAME} \xF0[\x90-\xBF][\x80-\xBF]{<span class="hljs-number">2</span>} [<span class="hljs-keyword">OR</span>]
RewriteCond %{REQUEST_FILENAME} [\xF1-\xF3][\x80-\xBF]{<span class="hljs-number">3</span>} [<span class="hljs-keyword">OR</span>]
RewriteCond %{REQUEST_FILENAME} \xF4[\x80-\x8F][\x80-\xBF]{<span class="hljs-number">2</span>}
RewriteCond %{REQUEST_FILENAME} !/bitrix/virtual_file_system.php$
RewriteRule ^(.*)$ /bitrix/virtual_file_system.php [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-l
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !/bitrix/urlrewrite.php$
RewriteRule ^(.*)$ /bitrix/urlrewrite.php [L]
RewriteRule .* - [E=REMOTE_USER:%{HTTP:Authorization}]
&lt;/IfModule&gt;

&lt;IfModule mod_dir.c&gt;
DirectoryIndex index.php index.html
&lt;/IfModule&gt;

&lt;IfModule mod_expires.c&gt;
ExpiresActive on
ExpiresByType image/jpeg <span class="hljs-string">"access plus 3 day"</span>
ExpiresByType image/gif <span class="hljs-string">"access plus 3 day"</span>
ExpiresByType image/png <span class="hljs-string">"access plus 3 day"</span>
ExpiresByType text/css <span class="hljs-string">"access plus 3 day"</span>
ExpiresByType application/javascript <span class="hljs-string">"access plus 3 day"</span>  
&lt;/IfModule&gt;

</pre><h2 role="button">Листинг 2. Включение журналирования ошибок</h2><p>Включение журналирования ошибок в файле /bitrix/.settings.php</p><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs php" spellcheck="false">...
    <span class="hljs-string">'readonly'</span> =&gt; <span class="hljs-keyword">false</span>,
  ),
  <span class="hljs-string">'exception_handling'</span> =&gt; 
  <span class="hljs-keyword">array</span> (
    <span class="hljs-string">'value'</span> =&gt; 
    <span class="hljs-keyword">array</span> (
      <span class="hljs-string">'debug'</span> =&gt; <span class="hljs-keyword">true</span>,
      <span class="hljs-string">'handled_errors_types'</span> =&gt; <span class="hljs-number">4437</span>,
      <span class="hljs-string">'exception_errors_types'</span> =&gt; <span class="hljs-number">4437</span>,
      <span class="hljs-string">'ignore_silence'</span> =&gt; <span class="hljs-keyword">false</span>,
      <span class="hljs-string">'assertion_throws_exception'</span> =&gt; <span class="hljs-keyword">true</span>,
      <span class="hljs-string">'assertion_error_type'</span> =&gt; <span class="hljs-number">256</span>,
      <span class="hljs-string">'log'</span> =&gt; <span class="hljs-keyword">array</span>(
        <span class="hljs-string">'settings'</span> =&gt; <span class="hljs-keyword">array</span>(
            <span class="hljs-string">'file'</span> =&gt; <span class="hljs-string">'bitrix/modules/error.log'</span>,
            <span class="hljs-string">'log_size'</span> =&gt; <span class="hljs-number">1000000</span>
        )
      ),
    ),
    <span class="hljs-string">'readonly'</span> =&gt; <span class="hljs-keyword">false</span>,
  ),
  <span class="hljs-string">'connections'</span> =&gt; 
  <span class="hljs-keyword">array</span> (
...
</pre><h2 role="button">Листинг 3. Файла .gitignore в корне сайта</h2><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs php" spellcheck="false">/bitrix/
/upload/
node_modules/

.ssh
history

/.htsecure
/.htaccess
/.htpasswd
.idea
.DS_Store

<span class="hljs-comment"># т.к. они отличаются для боевого сайта и dev</span>
robots.txt
Thumbs.db
*.log
*.tar
*.gz
*.tgz
*.tar.gz
*.zip
</pre><h2 role="button">Листинг 4. Файла .gitignore в каталоге /bitrix/</h2><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs sql" spellcheck="false">/<span class="hljs-keyword">backup</span>
/<span class="hljs-keyword">cache</span>
/crontab
/managed_cache
/stack_cache
/managed_flags
/tmp/
/html_pages
/php_interface/crontab

/php_interface/dbconn.php
/php_interface/after_connect.php
/php_interface/after_connect_d7.php
/.settings.php

*.log
</pre><h2 role="button">Листинг 5. Добавление файлов проекта в репозиторий</h2><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs sql" spellcheck="false">git init

<span class="hljs-comment"># конфигурация текущего репозтиория ДЛЯ СЕРВЕРА</span>
<span class="hljs-comment"># не нужно писать ключ --global, т.к. на сервере могут быть другие сайты</span>
git config user.name "здесь_указать_домен_сайта_без_http"
git config user.email "server@тот_же_самый_домен_сайта_без_http"
git config alias.st status
git config alias.co checkout

<span class="hljs-comment"># индексация всех файлов проекта</span>
git add .

<span class="hljs-comment"># фиксация всех файлов проекта</span>
git <span class="hljs-keyword">commit</span> -m <span class="hljs-string">"Init"</span>

<span class="hljs-comment"># добавление удалённого репозитория к проекту</span>
git remote <span class="hljs-keyword">add</span> origin удалённая_SSH_ссылка_на_репозиторий

<span class="hljs-comment"># отправка изменений в удалённый репозиторий</span>
git push <span class="hljs-comment">--set-upstream origin master</span>

<span class="hljs-comment"># если в удалённом репозитории уже есть история, в примеру,</span>
<span class="hljs-comment"># лежит файл README.md, то выйдетт ошибка</span>
<span class="hljs-comment"># как вариант, если в репозитории нет ничего нужно,</span>
<span class="hljs-comment"># можно заменить историю в удалённом репоитории на историю</span>
<span class="hljs-comment"># текущего проекта выполнив команду</span>
<span class="hljs-comment">#</span>
<span class="hljs-comment"># git push --force --set-upstream origin master</span>
<span class="hljs-comment"># </span>
<span class="hljs-comment"># предварительно нужно снять защиту с ветки master в настройках</span>
<span class="hljs-comment"># репозитория Settings -&gt; Repository -&gt; Unprotect</span>
<span class="hljs-comment"># после выполнения команды нужно снова защитить ветку master</span>
</pre><h2 role="button">Листинг 6. Добавление файлов ядра 1С-Битрикс в репозиторий</h2><p>Идея разделения файлов проекта и ядра на разные ветки/репозитории в том, что разработчик 99% времени работает только с файлами проекта, а файлы ядра вообще не должен править. Исключения файлов ядра из репозитория проекта существенно ускорит работу git при работе с веткой master и другими, унаследованными от неё.</p><p>Следующие команды нужно выполнять в каталоге <code role="button">/bitrix/</code> проекта:</p><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs sql" spellcheck="false">git init

<span class="hljs-comment"># конфигурация текущего репозтиория ДЛЯ СЕРВЕРА</span>
<span class="hljs-comment"># не нужно писать ключ --global, т.к. на сервере могут быть другие сайты</span>
git config user.name "здесь_указать_домен_сайта_без_http"
git config user.email "server@тот_же_самый_домен_сайта_без_http"
git config alias.st status
git config alias.co checkout

<span class="hljs-comment"># индексация всех файлов ядра</span>
git add .

<span class="hljs-comment"># фиксация всех файлов проекта</span>
git <span class="hljs-keyword">commit</span> -m <span class="hljs-string">"Init"</span>

<span class="hljs-comment"># создание и переключение в ветку bitrix-core</span>
git checkout -b bitrix-core

<span class="hljs-comment"># удаление ветки master</span>
git branch -d <span class="hljs-keyword">master</span>

<span class="hljs-comment"># добавление удалённого репозитория к папке ядра</span>
git remote <span class="hljs-keyword">add</span> origin SSH_ссылка_на_удалённый_репозиторий

<span class="hljs-comment"># отправка изменений в удалённый репозиторий</span>
git push <span class="hljs-comment">--set-upstream origin bitrix-core</span>
</pre><h2 role="button">Листинг 7. Добавление файла robots.txt.example</h2><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs" spellcheck="false">User-agent: *
Disallow: /
</pre></div>