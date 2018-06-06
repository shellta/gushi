## 📚 古诗文网爬虫

运行前准备：  
1. PHP 7+
2. MySQL 数据库
3. 修改 src/DB.php 中数据库用户名和密码

如何运行：

```
git clone git@github.com:shellta/gushi.git
cd gushi
mysql -uroot -p < gushi.sql // 导入数据库结构
composer update
php index.php // 开始
```

Todo：  
由于按照朝代分类超过1000页的内容无法访问，所以爬到的古诗文并不完整，下一步将按照作者分类进行爬取。
