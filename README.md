
<p align="center">
	<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

# Sign Language AI — Backend (Laravel 11 + Firestore)

<p align="center">
	<b>Quản lý nội dung học & từ điển ngôn ngữ ký hiệu, đồng bộ với ứng dụng Flutter, tích hợp Firestore, API contract rõ ràng, CI/CD hiện đại.</b>
</p>

---

## Giới thiệu dự án

Dự án **Sign Language AI** là hệ thống backend quản lý nội dung học tập và từ điển ngôn ngữ ký hiệu, phục vụ đồng bộ dữ liệu cho ứng dụng di động (Flutter). Hệ thống cung cấp:

- API đồng bộ nội dung học, từ điển, tiến trình học của người dùng
- Admin CMS quản trị chủ đề, bài học, từ vựng
- Tích hợp Google Firestore làm nguồn dữ liệu chính
- Quản lý version, kiểm tra checksum, publish nội dung an toàn
- API contract rõ ràng, tài liệu chi tiết, test tự động

## Điểm nổi bật

- **Clean Architecture**: Service Layer tách biệt, Controller gọn nhẹ, dễ maintain
- **Firestore Integration**: Đồng bộ dữ liệu real-time, tối ưu cho mobile offline
- **API Contract & Schema**: Tài liệu, JSON Schema, Postman collection đầy đủ
- **CI/CD hiện đại**: GitHub Actions build & deploy Cloud Run, scripts kiểm thử tự động
- **Testing & Quality**: Unit/Feature test, seeders, factories, scripts smoke test
- **Scalable & Secure**: Chuẩn Laravel 11, phân quyền rõ ràng, dễ mở rộng

## Cấu trúc thư mục

```
├── app/
│   ├── Http/Controllers/Api/...
│   ├── Http/Controllers/Admin/...
│   └── Services/Firestore/...
├── config/
├── database/
├── docs/ (API contract, schema, postman)
├── public/
├── resources/views/admin/
├── routes/ (api.php, web.php)
├── scripts/ (deploy, test, verify)
├── tests/ (Feature, Unit)
├── Dockerfile
└── .github/workflows/
```

## Công nghệ sử dụng

- **Laravel 11** (PHP 8.2)
- **Google Cloud Firestore** (google/cloud-firestore)
- **TailwindCSS, Vite** (frontend admin)
- **GitHub Actions** (CI/CD)
- **Docker** (Cloud Run ready)

## Hướng dẫn cài đặt & chạy local

```bash
# 1. Clone source & cài đặt PHP, Composer, Node.js
git clone https://github.com/your-org/sign-language-ai.git
cd sign-language-ai

# 2. Cài đặt backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

# 3. Cài đặt frontend assets
npm install
npm run build

# 4. Khởi động server
php artisan serve

# 5. Truy cập: http://127.0.0.1:8000/admin
```

## 🧪 Kiểm thử & scripts

- Chạy toàn bộ test: `php artisan test`
- Smoke test API: `./scripts/api_smoke_test.ps1`
- Test tạo từ điển: `./scripts/test_dict_create.ps1`

## 📄 Tài liệu & API Contract

- [docs/step-01-firestore-schema-and-contract.md](docs/step-01-firestore-schema-and-contract.md)
- [docs/contracts/](docs/contracts/)
- [docs/postman/](docs/postman/)

## Đóng góp & phát triển

Pull request luôn được chào đón! Vui lòng đọc tài liệu, tuân thủ coding convention, viết test cho chức năng mới.

## License

MIT. Copyright © 2026

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
