# "track" 是什么?
一个 PHP Web 框架.由于本人十分喜欢 laravel 这个框架,所以仔细研究了它的源码并运用于实践.
慢慢地不满足于只是看文档使用框架,想要根据自己的能力与使用习惯写一个属于自己的框架,完全由自己掌控,遇到问题利于解决.
最后有了此框架,仔细看源码和 laravel 的极其相似,事实上真的可以看做是 laravel 的简体中文精简版,添加了大量的注释,并且所有注释都为中文注释.
路由,控制器,中间件,cookie,session,数据库,缓存,帮助函数等等 Web 开发功能一应俱全.
现在虽然会有许多不足,但我会慢慢修改维护,目标就是给中国人使用的 Web 框架.

# 运行环境
* PHP >= 5.55.6.4
* PHP 扩展(PDO，pcntl， posix， redis)
* 参考 composer.json 文件

# 使用

生成一份项目的配置文件
```sh
mv .env.demo .env
```

```sh
#--------------------
#
# 配置文件内容如下,按需配置
#
#--------------------

############# 通用配置 ############
# 是否开启 debug 模式(开启后,响应中为详细错误信息)
APP_DEBUG=true
# 项目密钥,用于数据的加密解密( Cookie 已默认使用)
APP_KEY=base64:zeisX9GV8j4eHRZMuqggH4dB8KNpe2KqqvJ76j91X9U=
# 日志存放目录,当前 Web 用户需具有读写权限
# 如果不填默认为 storage/logs/ 目录
LOG_PATH=null

############# 数据库 ############
# 默认连接
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=xxx
DB_USERNAME=xxx
DB_PASSWORD=xxx

############# 缓存 #############
# 默认连接
CACHE_DRIVER=redis
CACHE_HOST=127.0.0.1
CACHE_PORT=6379

############# 微信开发 ############
WECHAT_OPEN_PLATFORM_APPID=xxx
WECHAT_OPEN_PLATFORM_SECRET=xxx
WECHAT_OPEN_PLATFORM_TOKEN=xxx
WECHAT_OPEN_PLATFORM_AES_KEY=xxx
```

# todo

* 请求参数验证器


