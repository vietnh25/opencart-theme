# opencart-theme
- Nếu định nghĩa lại màu: admin\controller\extension\module\soconfig.php
http://prntscr.com/vx7i6f
- Nếu có sửa style thì cần: 
+ vào So Themes Config >> Advanced >> http://prntscr.com/vx6mgw
+ tab General >> Compile CSS lại cho tất cả các layout http://prntscr.com/vx6n61


QUICKSTART

- Nếu đã checkout svn thì copy code sang folder mới để pack
- export db về. copy vào folder install, đổi tên thành opencart.sql
- Xóa content 2 file config.php và admin/config.php; content folder image\cache\
- zip lại


THEME
___chuẩn bị data___
- file admin\view\template\extension\soconfig\demo\default\install.php : nếu có mod mới liên quan đến table trong db thì bổ sung vào
- sample_opencart3.php, đổi tên thành db theme hiện tại, sau đó run để lấy file themes.sql, bỏ vào admin\view\template\extension\soconfig\demo\default

- admin\view\template\extension\soconfig\demo\layout1,2,3... (BƯỚC NÀY NẾU LÀ PACK LẠI SAU UPDATE, KO SỬA CONFIG CỦA CÁC LAYOUT THÌ KO CẦN LÀM)
open: admin\view\template\extension\soconfig\demo\default\theme.sql
find: INSERT INTO {table_prefix}soconfig VALUES sau đó tìm typelayout "typelayout\":\"1\" (1,2,3...tương ứng) rồi copy đoạn code đó http://prntscr.com/vx6yei
sang admin\view\template\extension\soconfig\demo\layout1,2,3... tương ứng. 
chú ý: đổi REPLACE và null như ảnh http://prntscr.com/vx6xjv

* themes.sql (so sánh với file cũ ng trc package)
- xóa data liên quan đến table information
https://luannt.tinytake.com/sf/Mjk0MzM4MV84ODM0Nzkx
- Nếu theme có so mobile, copy đoạn Mobile ở đầu vào http://prntscr.com/vx72x4
-> đổi id thành id page builder mobile tương ứng http://prntscr.com/vx74sq
(nếu update theme, thì chỉ cần so sánh file đã package copy sang)

- INSERT INTO {table_prefix}layout_module VALUES
xóa hêt 32 ko bị lỗi simple blog trên mobile layout
http://prntscr.com/vx77o6
http://prntscr.com/h5jlb7

- thay 1 số chỗ INSERT INTO thành REPLACE INTO --> so sánh file cũ...
INTO {table_prefix}module VALUES
INTO {table_prefix}extension VALUES
INTO {table_prefix}layout_module VALUES
INTO {table_prefix}setting VALUES

- color swatches - goi theme
delete 
("76","module","so_color_swatches_pro"),
("116","module","so_product_label")
trong REPLACE INTO {table_prefix}extension VALUES

- Table modification
CHÚ Ý export tool dấu cách bị sai >> MỞ opencart.sql và copy phần insert content của table này sang
http://prntscr.com/vx7d2d
xóa các dòng INSERT ở giữa, chú ý dấu , ; 
thêm vào đầu INSERT INTO {table_prefix}modification VALUES
Sau đó đặt id -> null, để ko bị trùng id
http://prntscr.com/nhghkd

___export package___
run export-theme-oc3.php rồi download pack theme tương ứng về

** theme nào có so product Bundles chú ý add code ở folder _Bundle_codeaddto_theme vào gói theme 
- so_icraft\catalog\view\javascript xóa 3 folder này đi cho nhẹ (bootstrap, font-awesome, jquery)
http://prntscr.com/vx7g8u


___file patch_language:  nếu ko có thay đổi gì thì ko cần làm lại, nếu pack mới thì xem cấu trúc của folder này rồi copy data tương ứng của theme hiện tại vào và zip


__đặt tên và commit__
đặt tên version, tên folder theme và quickstart xem các version trước và làm tương tự
