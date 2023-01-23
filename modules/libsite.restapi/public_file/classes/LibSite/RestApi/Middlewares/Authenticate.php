<?php

namespace LibSite\RestApi\Middlewares;

use LibSite\RestApi\Exceptions\NotAuthorizedHttpException;
use LibSite\RestApi\Pecee\Http\Middleware\IMiddleware;
use LibSite\RestApi\Pecee\Http\Request;
use LibSite\RestApi\UsersTable;

class Authenticate implements IMiddleware
{

    /**
     * @inheritDoc
     */
    public function handle(Request $request): void
    {
        $path = $request->getUrl()->getPath();
        $result = self::checkAccess($path);

        if(!$result){
            header('WWW-Authenticate: Basic realm="Backend"');
            header('HTTP/1.0 401 Unauthorized');
            throw new NotAuthorizedHttpException('Нет доступа к вызываемому методу '.$path);
        }

    }

    public static function checkAccess(string $path): bool{

        $result = false;
        $isAccessClass =  false;

        $arAccess = [];
        //$_SERVER['PHP_AUTH_USER'] = 'test';
        //$_SERVER['PHP_AUTH_PW'] = 'test';
        if(!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])){
            $arAccess = UsersTable::getList([
                'filter'=>[
                    'LOGIN'=>$_SERVER['PHP_AUTH_USER'],
                    'PASSWORD'=>$_SERVER['PHP_AUTH_PW'],
                    //'ACCESS_AREA'=>$path
                ],
                'limit'=>1
            ])->fetch();

            if(!empty($arAccess['ACCESS_AREA'])){
                $arAccess['ACCESS_AREA'] = explode(',', trim($arAccess['ACCESS_AREA']));
                foreach ($arAccess['ACCESS_AREA'] as $tmpAccessPath){
                    $tmpAccessPath = trim($tmpAccessPath);

                    if(
                        !empty($tmpAccessPath) &&
                        ($tmpAccessPath==$path || strpos($path, $tmpAccessPath)!==false)
                    )
                    {
                        $isAccessPath = true;
                        break;
                    }
                }
            }
            else{
                $isAccessPath = true;
            }
        }

        if(!empty($_SERVER['PHP_AUTH_USER']) &&
            ($_SERVER['PHP_AUTH_PW']==$arAccess['PASSWORD']) &&
            (strtolower($_SERVER['PHP_AUTH_USER'])==$arAccess['LOGIN']) &&
            $isAccessPath
        )
        {
            $result = true;
        }

        return $result;
    }
}