<div class="article-content"><ol><li>Отключить агенты на хитах (см. листинг 1)</li><li>В /bitrix/php_interface/dbconn.php&nbsp;удалить определение констант BX_CRONTAB и&nbsp;BX_CRONTAB_SUPPORT. Добавить определение константы BX_CRONTAB_SUPPORT&nbsp;для cli (см. листинг 2)</li><li>Создать файл&nbsp;/bitrix/php_interface/cron_events.php</li><li>Добавить задание в cron (см. листинг 4)</li></ol><h2 role="button">Листинг 1. Отключение агентов на хитах</h2><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs php" spellcheck="false"><span class="hljs-comment"># в PHP-консоли сайта выполнить</span>

COption::SetOptionString(<span class="hljs-string">"main"</span>, <span class="hljs-string">"agents_use_crontab"</span>, <span class="hljs-string">"N"</span>);
<span class="hljs-keyword">echo</span> COption::GetOptionString(<span class="hljs-string">"main"</span>, <span class="hljs-string">"agents_use_crontab"</span>, <span class="hljs-string">"N"</span>);

COption::SetOptionString(<span class="hljs-string">"main"</span>, <span class="hljs-string">"check_agents"</span>, <span class="hljs-string">"N"</span>);
<span class="hljs-keyword">echo</span> COption::GetOptionString(<span class="hljs-string">"main"</span>, <span class="hljs-string">"check_agents"</span>, <span class="hljs-string">"Y"</span>);
</pre><h2 role="button">Листинг 2. Настройка dbconn.php</h2><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs php" spellcheck="false"><span class="hljs-comment"># удалеить определение констант BX_CRONTAB</span>
<span class="hljs-comment"># и BX_CRONTAB_SUPPORT</span>

<span class="hljs-comment"># добавить</span>

<span class="hljs-keyword">if</span>(!(defined(<span class="hljs-string">"CHK_EVENT"</span>) &amp;&amp; CHK_EVENT===<span class="hljs-keyword">true</span>))
define(<span class="hljs-string">"BX_CRONTAB_SUPPORT"</span>, <span class="hljs-keyword">true</span>);
</pre><h2 role="button">Листинг 3. Файл cron_events.php</h2><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs php" spellcheck="false"><span class="hljs-meta">&lt;?php</span>
$_SERVER[<span class="hljs-string">"DOCUMENT_ROOT"</span>] = realpath(dirname(<span class="hljs-keyword">__FILE__</span>).<span class="hljs-string">"/../.."</span>);
$DOCUMENT_ROOT = $_SERVER[<span class="hljs-string">"DOCUMENT_ROOT"</span>];

define(<span class="hljs-string">"NO_KEEP_STATISTIC"</span>, <span class="hljs-keyword">true</span>);
define(<span class="hljs-string">"NOT_CHECK_PERMISSIONS"</span>,<span class="hljs-keyword">true</span>);
define(<span class="hljs-string">'BX_NO_ACCELERATOR_RESET'</span>, <span class="hljs-keyword">true</span>);
define(<span class="hljs-string">'CHK_EVENT'</span>, <span class="hljs-keyword">true</span>);
define(<span class="hljs-string">'BX_WITH_ON_AFTER_EPILOG'</span>, <span class="hljs-keyword">true</span>);

<span class="hljs-keyword">require</span>($_SERVER[<span class="hljs-string">"DOCUMENT_ROOT"</span>].<span class="hljs-string">"/bitrix/modules/main/include/prolog_before.php"</span>);

@set_time_limit(<span class="hljs-number">0</span>);
@ignore_user_abort(<span class="hljs-keyword">true</span>);

CAgent::CheckAgents();
define(<span class="hljs-string">"BX_CRONTAB_SUPPORT"</span>, <span class="hljs-keyword">true</span>);
define(<span class="hljs-string">"BX_CRONTAB"</span>, <span class="hljs-keyword">true</span>);
CEvent::CheckEvents();

<span class="hljs-keyword">if</span>(CModule::IncludeModule(<span class="hljs-string">'sender'</span>))
{
\Bitrix\Sender\MailingManager::checkPeriod(<span class="hljs-keyword">false</span>);
\Bitrix\Sender\MailingManager::checkSend();
}

<span class="hljs-keyword">require</span>($_SERVER[<span class="hljs-string">'DOCUMENT_ROOT'</span>].<span class="hljs-string">"/bitrix/modules/main/tools/backup.php"</span>);
CMain::FinalActions();
<span class="hljs-meta">?&gt;</span>
</pre><h2 role="button">Листинг 4. Добавление задания cron</h2><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs php" spellcheck="false"><span class="hljs-comment"># в phpinfo необходимо посмотреть расположение php</span>
<span class="hljs-comment"># и расположение конфигурационного файла для запуска</span>
<span class="hljs-comment"># php в пунктах "Configuration File"</span>
<span class="hljs-comment"># и "Loaded Configuration File" соответственно</span>
<span class="hljs-comment"># пример</span>
<span class="hljs-comment"># Configuration File: /opt/php74/etc</span>
<span class="hljs-comment"># Loaded Configuration File: /var/www/php-bin-isp-php74/dev_velocityk/php.ini</span>

<span class="hljs-comment"># в результате добавить задание таким образом изменив путь до</span>
<span class="hljs-comment"># cron_event.php на актуальный</span>

*/<span class="hljs-number">1</span> * * * * /opt/php74/bin/php -c /<span class="hljs-keyword">var</span>/www/php-bin-isp-php74/dev_velocityk/php.ini /home/bitrix/www/bitrix/php_interface/cron_events.php
</pre></div>