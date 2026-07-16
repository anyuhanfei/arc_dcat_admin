1. 执行命令：git clone https://github.com/anyuhanfei/arc_dcat_admin.git
2. 复制 .env.example 为 .env(改为命令执行)
3. 修改 .env 文件中的数据库信息和项目名
4. 执行命令：composer install
5. 执行命令：php artisan key:generate
6. 执行命令：php artisan admin:publish
7. 执行命令：php artisan admin:install