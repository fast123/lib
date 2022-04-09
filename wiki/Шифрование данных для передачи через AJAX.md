<div class="article-content"><p>Иногда возникает необходимость передать важные данные (ВД) c сервера в javascript, а из него в нужный момент сделать ajax-запрос к обработчику запроса на сервере. При этом&nbsp; необходимо обеспечить передачу важных данных (ВД) в неизменном виде и исключить возможность их подмены злоумышленником.</p><p>Несколько примеров использования:</p><ol><li>Необходимо убедиться, что ajax-запрос выполняется тем же пользователем сайта для которого сформирована страница.</li><li>Нужно передать параметры вызова текущего компонента $arParams, чтобы в обработчике ajax-запроса сделать вызов этого компонента с этими же параметрами $arParams. К примеру, это можно использовать для работы кнопки "Показать ещё". Так же это используется для передачи $arParams в компоненте оформления заказа sale.order.ajax (собственно оттуда это и взято). Пояснение на примере работы кнопки "Показать ещё" при многостраничном выводе элементов: происходит вызов компонента, формирующего список элементов. Для того чтобы вывести элементы следующей страницы под текущими элементами по нажатию на кнопку "Показать ещё" потребуется сделать ajax-запрос к скрипту, который вызовет этот же компонент с этими же параметрами, но для страницы со следующим номером. Конечно, можно продублировать значения параметров вызова компонента в скрипте обрабатывающем ajax-запрос. Но тогда всегда придётся вручную поддерживать эти параметры. К примеру, если в параметрах вызова основного компонента сменили число отображаемых на странице элементов, то придётся не забыть поменять этот параметр и в обработчике ajax, иначе при клике по кнопке "Показать ещё" будет подгружаться другое количество элементов. Если же этот параметр меняет пользователь через административный интерфейс, то в ajax-обработчике значение само себя не поменяет и подгрузка новых элементов будет работать неправильно. Выходом может быть передача $arParams в зашифрованном виде в javascript, а из него в условный ajax.php.</li><li>...</li></ol><p>Для шифрования и расшифровки сообщений в 1C-Bitrix есть специальный класс&nbsp;<code style="font-size: inherit;" role="button">\Bitrix\Security\Sign\Signer</code></p><p>К примеру, в template.php можно зашифровать ключом <code role="button">
key1</code> важные данные и передать это сообщение в script.js вместе не зашифрованными данными необходимыми для работы js-скрипта. При выполнении AJAX-запроса из script.js вместе с обычными параметрами запроса нужно отправить эту зашифрованную строку. Теперь в ajax.php с помощью ключа&nbsp;<code role="button">key1</code>&nbsp; можно расшифровать зашифрованные данные и использовать по назначению.</p><p>template.php</p><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs xml" spellcheck="false"><span class="php"><span class="hljs-meta">&lt;?</span>
...
$secretMessage = <span class="hljs-keyword">array</span>(
    <span class="hljs-string">'p1'</span> =&gt; <span class="hljs-number">10</span>
);

$signer = <span class="hljs-keyword">new</span> \Bitrix\Main\Security\Sign\Signer;
$encryptedMessage = $signer-&gt;sign(base64_encode(serialize($secretMessage)), <span class="hljs-string">'key1'</span>);
<span class="hljs-meta">?&gt;</span></span>
<span class="hljs-tag">&lt;<span class="hljs-name">script</span>&gt;</span><span class="xml">
BX.message({
encrypted_msg: '<span class="php"><span class="hljs-meta">&lt;?</span>= \CUtil::JSEscape($encryptedMessage)<span class="hljs-meta">?&gt;</span></span>'
});
</span><span class="hljs-tag">&lt;/<span class="hljs-name">script</span>&gt;</span>
</pre><p>script.js</p><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs javascript" spellcheck="false">...
$.post(
    <span class="hljs-string">'/ajax.php'</span>,
    {
        <span class="hljs-attr">param1</span>: param1,
        <span class="hljs-attr">param2</span>: param2,
        <span class="hljs-attr">encrypted_msg</span>: BX.message(<span class="hljs-string">'encrypted_msg'</span>)
    }
);
...
</pre><p>ajax.php</p><i class="mi ql-syntax-copy" role="button">filter_none</i><pre class="ql-syntax hljs php" spellcheck="false"><span class="hljs-meta">&lt;?</span><span class="hljs-keyword">require</span>($_SERVER[<span class="hljs-string">"DOCUMENT_ROOT"</span>] . <span class="hljs-string">"/bitrix/modules/main/include/prolog_before.php"</span>);

<span class="hljs-keyword">use</span> \<span class="hljs-title">Bitrix</span>\<span class="hljs-title">Main</span>\<span class="hljs-title">Application</span>;

$request = Application::getInstance()-&gt;getContext()-&gt;getRequest();

$signer = <span class="hljs-keyword">new</span> \Bitrix\Main\Security\Sign\Signer;
<span class="hljs-keyword">try</span>
{
$encryptedMessage = $request-&gt;get(<span class="hljs-string">'encrypted_msg'</span>) ?: <span class="hljs-string">''</span>;
$decryptedMessage = $signer-&gt;unsign($encryptedMessage, <span class="hljs-string">'key1'</span>);
$secretMessage = unserialize(base64_decode($decryptedMessage));
}
<span class="hljs-keyword">catch</span> (\Bitrix\Main\Security\Sign\BadSignatureException $e)
{
<span class="hljs-comment"># сообщение не расшифровывается =&gt; данные не достоверные =&gt; не обрабатываем это</span>
<span class="hljs-keyword">die</span>();
}

<span class="hljs-comment"># работаем с переданными параметрами</span>
<span class="hljs-keyword">echo</span> <span class="hljs-string">'&lt;pre&gt;'</span>;
print_r($secretMessage);
<span class="hljs-keyword">echo</span> <span class="hljs-string">'&lt;/pre&gt;'</span>;
</pre></div>