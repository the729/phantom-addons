# 门磁打开超时提醒

## 安装要求

- PHP5以上
- MySQL
- beanstalkd在localhost上默认端口运行

## 安装方法

- 在MySQL中建立数据库，导入phantom_sushi_notify.sql建立数据库结构
- 修改inc/conf.template.php，需要修改如下：
    - 数据库用户名密码
    - 数据库名称（如果没有用默认的话）
    - App ID和App Secret
    - App Base指向本应用的根路径的URL地址，用于构造OAuth2的重定向地址
- 确保notify-log.txt可以被运行服务器的账号修改，建议chmod a+rw权限
