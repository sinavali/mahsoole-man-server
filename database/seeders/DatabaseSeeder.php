<?php

namespace Database\Seeders;

use App\Models\Activities\Discount;
use App\Models\Logs\Log;
use App\Models\Products\Category;
use App\Models\Products\CategoryProduct;
use App\Models\Products\Product;
use App\Models\Users\Admin\Admin;
use App\Models\Users\Operator\Operator;
use App\Models\Users\User\User;
use App\Models\Users\UserMeta;
use App\Models\Users\Vendor\Vendor;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Tags\Tag;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // define permissions
        $pp = ['addProduct', 'editProduct', 'deleteProduct', 'activateProduct', 'deactiveProduct'];
        $op = ['addOperator', 'editOperator', 'deleteOperator', 'activateOperator', 'deactiveOperator'];
        $vp = ['addVendor', 'editVendor', 'deleteVendor', 'activateVendor', 'deactiveVendor'];
        $cartp = ['addToCart', 'removeFromCart', 'changeQuantityInCart'];
        $discountp = ['addDiscount', 'editDiscount', 'deleteDiscount'];
        $paymentp = ['pay', 'tracePayment'];
        $permissions = [...$pp, ...$op, ...$vp, ...$cartp, ...$discountp, ...$paymentp];
        //
        foreach ($permissions as $p)
            Permission::create(['name' => $p]);
        //
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'operator']);
        Role::create(['name' => 'vendor']);
        Role::create(['name' => 'customer']);
        //
        Role::findByName('admin')->givePermissionTo($permissions);
        Role::findByName('operator')->givePermissionTo(array_diff($permissions, ['pay', 'addProduct', 'deleteProduct', 'addVendor', 'deleteVendor', 'addToCart', 'changeQuantityInCart', ...$op, ...$discountp]));
        Role::findByName('vendor')->givePermissionTo(array_diff($permissions, ['activateProduct', 'deactiveProduct', ...$op, ...$vp, ...$cartp, ...$paymentp]));
        Role::findByName('customer')->givePermissionTo([...$paymentp, ...$cartp]);
        // create users
        $temp = Admin::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389399'
        ]);
        $temp->assignRole('admin');

        Operator::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389399'
        ])->assignRole('operator');
        Operator::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389397',
            'active' => 0
        ]);
        Operator::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389384',
            'active' => 0
        ]);
        Operator::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389383'
        ]);
        $vendor1 = Vendor::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389399',
            'active' => 1,
            'slug' => 'my-shop-1',
            'title' => 'my shop 1'
        ])->assignRole('vendor');
        $vendor2 = Vendor::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389395',
            'slug' => 'my-shop-2',
            'title' => 'my shop 2'
        ]);
        $vendor3 = Vendor::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389394',
            'active' => 1,
            'slug' => 'my-shop-3',
            'title' => 'my shop 3'
        ]);
        $vendor4 = Vendor::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389393',
            'slug' => 'my-shop-4',
            'title' => 'my shop 4'
        ]);
        User::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389399'
        ])->assignRole('customer');
        User::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389391'
        ]);
        User::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389390'
        ]);
        User::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389389'
        ]);
        User::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389388'
        ]);
        User::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389387'
        ]);
        User::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389386'
        ]);
        User::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389385'
        ]);
        User::create([
            'email' => 'sina1vali@gmail.com',
            'email_confirmed' => 1,
            'mobile' => '9015389382'
        ]);
        // create products
        Product::create([
            'title' => 'محصول شماره 1',
            'vendor_uuid' => $vendor1->uuid,
            'status' => 'draft',
            'price' => 2500000
        ]);
        Product::create([
            'title' => 'محصول شماره 2',
            'vendor_uuid' => $vendor1->uuid,
            'status' => 'draft',
            'price' => 2000000
        ]);
        Product::create([
            'title' => 'محصول شماره 3',
            'vendor_uuid' => $vendor1->uuid,
            'active' => 1,
            'price' => 4350000
        ]);
        Product::create([
            'title' => 'محصول شماره 4',
            'vendor_uuid' => $vendor1->uuid,
            'active' => 1,
            'price' => 1700000
        ]);
        Product::create([
            'title' => 'محصول شماره 1',
            'vendor_uuid' => $vendor2->uuid,
            'status' => 'draft',
            'price' => 2900000
        ]);
        Product::create([
            'title' => 'محصول شماره 2',
            'vendor_uuid' => $vendor2->uuid,
            'status' => 'draft',
            'price' => 2100000
        ]);
        Product::create([
            'title' => 'محصول شماره 3',
            'vendor_uuid' => $vendor2->uuid,
            'active' => 1,
            'price' => 4550000
        ]);
        Product::create([
            'title' => 'محصول شماره 4',
            'vendor_uuid' => $vendor2->uuid,
            'active' => 1,
            'price' => 1600000
        ]);
        Product::create([
            'title' => 'محصول شماره 1',
            'vendor_uuid' => $vendor4->uuid,
            'status' => 'draft',
            'price' => 1900000
        ]);
        Product::create([
            'title' => 'محصول شماره 2',
            'vendor_uuid' => $vendor4->uuid,
            'status' => 'draft',
            'price' => 4100000
        ]);
        Product::create([
            'title' => 'محصول شماره 3',
            'vendor_uuid' => $vendor4->uuid,
            'active' => 1,
            'price' => 5550000
        ]);
        Product::create([
            'title' => 'محصول شماره 4',
            'vendor_uuid' => $vendor4->uuid,
            'active' => 1,
            'price' => 1000000
        ]);
        // create discount
        Discount::create([
            'vendor_uuid' => $vendor1->uuid,
            'type' => 'unit',
            'code' => 'unit-code',
            'amount' => 500000,
            'include_shipping' => 0,
        ]);
        Discount::create([
            'vendor_uuid' => $vendor1->uuid,
            'type' => 'percent',
            'code' => 'percent-code',
            'amount' => 30,
            'include_shipping' => 1,
        ]);
        // log this seeder
        Log::create([
            'model' => 'seeder',
            'by' => 'system',
            'action' => 'seeding',
            'message' => 'Seeding was successful',
            'error' => 0,
        ]);
        // set some user metas for vendors (vendor 1)
        UserMeta::create([
            'relation_uuid' => $vendor1->uuid,
            'meta_key' => 'vendor_owner_first_name',
            'meta_value' => 'سینا'
        ]);
        UserMeta::create([
            'relation_uuid' => $vendor1->uuid,
            'meta_key' => 'vendor_owner_last_name',
            'meta_value' => 'ولی زاده'
        ]);
        UserMeta::create([
            'relation_uuid' => $vendor1->uuid,
            'meta_key' => 'vendor_owner_mobile',
            'meta_value' => '9015389399'
        ]);
        UserMeta::create([
            'relation_uuid' => $vendor1->uuid,
            'meta_key' => 'vendor_shop_name',
            'meta_value' => 'فروشگاه تقی و برادران به جز ممد'
        ]);
        UserMeta::create([
            'relation_uuid' => $vendor1->uuid,
            'meta_key' => 'vendor_state',
            'meta_value' => 'گلستان'
        ]);
        UserMeta::create([
            'relation_uuid' => $vendor1->uuid,
            'meta_key' => 'vendor_city',
            'meta_value' => 'گرگان'
        ]);
        UserMeta::create([
            'relation_uuid' => $vendor1->uuid,
            'meta_key' => 'vendor_address',
            'meta_value' => 'خیابان سرخواجه - مجتمع آفتاب 3 - طبقه 2 - واحد 259'
        ]);
        UserMeta::create([
            'relation_uuid' => $vendor1->uuid,
            'meta_key' => 'vendor_merchant_code',
            'meta_value' => ''
        ]);
        UserMeta::create([
            'relation_uuid' => $vendor1->uuid,
            'meta_key' => 'vendor_support_mobile',
            'meta_value' => '9015389399'
        ]);
        UserMeta::create([
            'relation_uuid' => $vendor1->uuid,
            'meta_key' => 'vendor_support_mobile_verified',
            'meta_value' => '1'
        ]);
        UserMeta::create([
            'relation_uuid' => $vendor1->uuid,
            'meta_key' => 'vendor_city',
            'meta_value' => 'گرگان'
        ]);
        // // create categories
        $category1 = Category::create([
            'vendor_uuid' => $vendor1->uuid,
            'title' => 'دسته بندی 1',
        ]);
        Category::create([
            'vendor_uuid' => $vendor1->uuid,
            'title' => 'دسته بندی 2',
        ]);
        Category::create([
            'vendor_uuid' => $vendor1->uuid,
            'title' => 'دسته بندی 3',
        ]);
        $category2 = Category::create([
            'vendor_uuid' => $vendor1->uuid,
            'title' => 'دسته بندی 4',
        ]);
        $category3 = Category::create([
            'vendor_uuid' => $vendor1->uuid,
            'title' => 'دسته بندی 5',
        ]);
        $mainProduct = Product::create([
            'uuid' => '214311842437',
            'title' => 'لپ تاپ 15.6 اینچی ایسر مدل Aspire 3 A315-510P-3652 - i3 4GB 512SSD - کاستوم شده',
            'vendor_uuid' => $vendor1->uuid,
            'active' => 1,
            'status' => 'published',
            'price' => 15800000,
            'off_price' => 15400000,
            'quantity' => 10,
            'sku' => 'ishop-50001',
            'content' => '<p>شرکت «ایسر» یکی از شرکت‌های فعال در زمینه‌ی تولید لپ‌تاپ است که طیف وسیع محصولاتش براساس نیاز مشتری ساخته شده و این یکی از ویژگی‌هایی است که باعث شده این شرکت در ایران طرفداران زیادی داشته باشد. لپ‌تاپ مدل «‌ Aspire 3 A315-510P-3652» یکی از مدل‌های زیبا، ظریف و بسیار باکیفیت است. پردازنده‌ی مرکزی i3 اینتل با مدل N305 پردازش محاسبات را برعهده دارد.4 گیگابایت حافظه‌ی رم و 512 گیگابایت حافظه از نوع SSD (درایو حالت جامد) (Solid State Drive) برای ذخیره و نقل‌و‌انتقال اطلاعات استفاده می‌شود و مناسب به‌نظر می‌رسد. صفحه‌نمایش آن 15.6 اینچی با دقت‌ FHD است و روکش ماتش هنگام کار در محیط‌های باز و تابش مستقیم نور آفتاب ممکن است . این دستگاه از لپ‌تاپ‌های نسبتا باریک ایسر است؛ همین مسئله حذف درایو نوری را توجیه می‌کند. وجود پورت‌ HDMI، دو پورت‌ USB 3.2‌ و یک USB 2.0 می‌تواند نیاز شما را برطرف کند؛ ولی برای استفاده از پورت‌های بیشتر می‌توانید از یک هاب استفاده کنید. مشخصات سخت‌افزاری، این دستگاه را در رده‌ی کاربری عمومی قرار می‌دهد و باتری 4 سلولی آن که از نوع لیتیوم‌پلیمر است و برای کارهای روزمره بسیار خوب به نظر می‌رسد و تا 4 ساعت شارژدهی دستگاه را تامین می‌کند. این مدل کاستوم شده و توسط شرکت واردکننده ارتقا یافته است.</p>',
            'created_at' => '2023-10-05 13:23:31',
            'updated_at' => '2023-10-06 13:06:03',
        ]);
        // CategoryProduct::create([
        //     'category_id' => $category1->id,
        //     'product_uuid' => $mainProduct->uuid,
        // ]);
        // CategoryProduct::create([
        //     'category_id' => $category2->id,
        //     'product_uuid' => $mainProduct->uuid,
        // ]);
        // CategoryProduct::create([
        //     'category_id' => $category3->id,
        //     'product_uuid' => $mainProduct->uuid,
        // ]);
    }
}