# API examples

## Create enquiry

```http
POST /api/enquiries
Content-Type: application/json

{
  "tour_id": 1,
  "name": "Nguyen Van A",
  "email": "a@example.com",
  "phone": "+84901234567",
  "preferred_month": "2026-11",
  "message": "Please send me more information.",
  "status": "booked"
}
```

Even when the client sends `status`, the created enquiry is always `new`.

## Update status

```http
PATCH /api/enquiries/1/status
Content-Type: application/json

{
  "status": "contacted"
}
```

## List enquiries

```http
GET /api/enquiries
```

The listing returns a pagination object instead of a bare array. Read enquiries
from `data`. Each page contains at most 20 records, ordered by descending `id`.
Request subsequent pages with `GET /api/enquiries?page=2`.
Use `next_page_url` and `prev_page_url` to navigate; a null link means there is
no page in that direction. `current_page` and `per_page` describe the page.
No total count is calculated. Each item retains `id`, `name`, `email`,
`tour_name`, and `status`. Invalid page values return HTTP 422.