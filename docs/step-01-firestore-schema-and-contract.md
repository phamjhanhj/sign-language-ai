# Step 01 - Chốt Firestore Schema và API Contract (Flutter ↔ Laravel)

Tài liệu này là **nguồn sự thật** cho bước 1: thống nhất cấu trúc dữ liệu trong Firestore và JSON contract trả về cho Flutter.

## 1) Mục tiêu

- Dữ liệu học và từ điển được quản lý trên Firestore.
- Flutter học offline bằng Isar, chỉ gọi API khi cần đồng bộ version.
- Laravel làm backend API + admin CMS + publish version.

## 2) Firestore Collections (chuẩn đề xuất)

### 2.1 Metadata & Versioning

- `content_meta/current`
  - `version` (number)
  - `checksum` (string)
  - `published_at` (timestamp)
  - `dictionary_version` (number)
  - `learning_version` (number)

- `content_versions/{version}`
  - `version` (number)
  - `checksum` (string)
  - `published_at` (timestamp)
  - `published_by` (string)
  - `notes` (string|null)

### 2.2 Learning Content

- `learning_topics/{topicCloudId}`
  - `cloud_id`, `title`, `description`, `thumbnail_url`, `order_index`, `is_active`, `updated_at`

- `learning_topics/{topicCloudId}/lessons/{lessonCloudId}`
  - `cloud_id`, `title`, `description`, `order_index`, `topic_cloud_id`, `updated_at`

- `learning_topics/{topicCloudId}/lessons/{lessonCloudId}/questions/{questionCloudId}`
  - `cloud_id`, `type`, `order_index`, `related_vocab_ids`, `data`, `updated_at`

> Ghi chú: `data` là object động theo `type`, thay cho lưu JSON string trong cloud.

### 2.3 Dictionary Content

- `dictionary_topics/{dictionaryTopicCloudId}`
  - `cloud_id`, `name`, `icon_url`, `order_index`, `updated_at`

- `dictionary_topics/{dictionaryTopicCloudId}/vocabularies/{vocabCloudId}`
  - `cloud_id`, `word`, `video_url`, `definition`, `image_preview`, `topic_cloud_id`, `updated_at`

### 2.4 User Progress (giai đoạn sau)

- `users/{uid}`
  - profile + streak + points

- `users/{uid}/lesson_progress/{lessonCloudId}`
  - `lesson_cloud_id`, `topic_cloud_id`, `is_completed`, `score`, `completed_at`, `updated_at`

- `users/{uid}/study_sessions/{sessionId}`
  - batch kết quả upload sau khi học offline

## 3) Question Types Contract

Dùng đúng 3 loại hiện tại:

- `learn`
  - `data.video_url` (string)
  - `data.explanation` (string)

- `choice`
  - `data.video_url` (string)
  - `data.correct_answer` (string)
  - `data.options` (string[])

- `arrange`
  - `data.video_url` (string)
  - `data.correct_sentence` (string)
  - `data.shuffled_words` (string[])

## 4) Public API Contract (cho Flutter)

### 4.1 GET `/api/content/version`

Response 200:

```json
{
  "version": 4,
  "checksum": "sha256:...",
  "published_at": "2026-03-01T10:00:00Z"
}
```

### 4.2 GET `/api/content/bootstrap`

Response 200 trả full snapshot theo contract giống dữ liệu Flutter đang dùng:

```json
{
  "system_info": {
    "data_version": 4,
    "last_updated": "2026-03-01T10:00:00Z",
    "description": "Published snapshot"
  },
  "topics": []
}
```

### 4.3 GET `/api/dictionary/bootstrap`

Response 200:

```json
{
  "dictionary_info": {
    "version": 1,
    "last_updated": "2026-03-01"
  },
  "topics": []
}
```

## 5) Quy tắc version/publish

- Chỉ dữ liệu `published` được trả cho app.
- Mỗi lần thêm/sửa/xóa content qua admin, hệ thống ghi draft.
- Khi bấm Publish:
  1. Tăng `content_meta/current.version`.
  2. Tạo `content_versions/{version}`.
  3. Tính lại `checksum` toàn bộ snapshot.
- Flutter login:
  1. gọi `/api/content/version`.
  2. nếu khác local version thì gọi `/api/content/bootstrap` (và `/api/dictionary/bootstrap` nếu tách version).

## 6) Mapping sang Isar Flutter

- `cloud_id` trong Firestore ↔ `cloudId` trong Isar.
- `type` question trong Firestore (`learn|choice|arrange`) ↔ `QuestionType` của Flutter.
- `data` object từ server có thể serialize thành `dataJson` cho Isar nếu app vẫn giữ model cũ.

## 7) Quy ước kỹ thuật bắt buộc

- Mọi document đều có `cloud_id` duy nhất.
- Timestamps server-side: ưu tiên Firestore timestamp (`updated_at`, `published_at`).
- Không đổi key contract hiện có của Flutter (`cloud_id`, `order_index`, `related_vocab_ids`, ...).
- Không trả dữ liệu user trong bootstrap content.
