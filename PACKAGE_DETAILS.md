# KOS Cookie Consent Package

## Package ทำอะไรได้บ้าง

- แสดง Cookie Banner และ Preference Modal อัตโนมัติบนหน้าเว็บ โดยไม่ต้องวาง block เอง
- จัดการ Cookie Category, Tracking Code, Popup Copy, Logs และ Export ได้จาก Dashboard
- รองรับหลายภาษาและหลาย site
- มีปุ่ม `Accept All`, `Reject All` และ `Save Preferences`
- ผู้ใช้งานกลับมาแก้ค่าภายหลังได้ผ่าน `Cookie Settings`
- โหลด tracking code เฉพาะ category ที่ได้รับ consent แล้วเท่านั้น
- รองรับ Google Consent Mode V2 (ปิดไว้เป็นค่าเริ่มต้น เปิดได้จากหน้า Setting)
- สร้าง category เริ่มต้นให้ 4 ตัว: Necessary, Statistics, Preferences, Marketing

## พฤติกรรมปัจจุบันของระบบ

- optional cookies เริ่มต้นเป็น `false` ตามหลัก opt-in
- `Reject All` ไม่ใช่แค่ปิด popup แต่จะบันทึกว่า user ตอบแล้ว
- `Save Preferences` ที่เลือกทุกอย่างเป็น off ก็ถือว่า valid และบันทึกได้
- ถ้า user ถอน consent จาก category ที่เคย allow ระบบจะลบ first-party cookies ที่ map ไว้ให้ category นั้น แล้ว reload หน้า
- หลังตอบแล้ว banner ไม่ควรเด้งใหม่ ยกเว้น consent version เปลี่ยน หรือ browser นั้นกลายเป็น identity ใหม่

## ระบบผูก consent กับอะไร

ตอนนี้ package ไม่ได้ผูกกับ IP แล้ว

- ผูกหลักกับ first-party cookie ชื่อ `kos_cookie_consent_id`
- ผูกเพิ่มกับ `site_id`
- ผูกเพิ่มกับ `consent_version`

ผลลัพธ์คือ:

- browser คนละตัว = คนละ consent
- อุปกรณ์คนละเครื่อง = คนละ consent
- incognito/private window = โดยทั่วไปเป็นคนละ consent
- user หลายคนที่ใช้ browser profile เดียวกัน = แชร์ consent เดียวกัน

## เก็บข้อมูลอะไรบ้าง

บันทึกลงฐานข้อมูล:

- `consent_id`
- `IP Address`
- `site_id`
- `consent_version`
- cookie/category ที่ผู้ใช้ยอมรับ
- เวลาที่บันทึก

หมายเหตุสำคัญ:

- `IP Address` ตอนนี้เก็บไว้เพื่อ audit/check ย้อนหลังเท่านั้น
- ระบบไม่ได้ใช้ `IP` เป็นตัวตัดสินว่าคนนี้เคย accept หรือ reject แล้วหรือยัง

## หน้า Dashboard ที่มี

- `/dashboard/cookie_consent`
- `/dashboard/cookie_consent/logs`
- `/dashboard/cookie_consent/setting`
- `/dashboard/cookie_consent/popup_content`
- `/dashboard/cookie_consent/export`

## หน้า Popup Content จัดการอะไรได้

- Banner title
- Banner summary
- Preference panel title
- Preference panel intro (paragraph 1)
- Preference panel intro (paragraph 2)
- Preferences heading
- ปุ่ม `Save Preferences`
- ปุ่ม `Accept All`
- ปุ่ม `Reject All`
- ข้อความ `Cookie Settings`

หมายเหตุ:

- `Preference panel intro (paragraph 2)` เป็น optional แล้ว
- ถ้าไม่กรอก ระบบจะไม่แสดง paragraph ที่ 2 บน frontend

## Logs และ Export

ตอนนี้หน้า Logs และไฟล์ CSV export แสดงข้อมูล:

- `Consent ID`
- `IP Address`
- สถานะ YES/NO ของแต่ละ cookie category
- `Consent Version`
- เวลา `Created`

## Google Consent Mode V2 (0.3.0)

- เปิด/ปิดได้ที่ `/dashboard/cookie_consent/setting` การ์ด "Google Consent Mode V2" ค่าเริ่มต้นคือปิด
- ตั้งค่าได้: เปิดใช้งาน, Google tag ID (`G-`, `AW-`, `GTM-` ใส่ได้หลายตัวคั่นด้วย comma), `ads_data_redaction`, `url_passthrough` ส่วน `wait_for_update` ตายตัว 500 ms ในโค้ด
- แต่ละ cookie category เลือกได้ว่าควบคุม signal ไหน (`consent_mode_types`) เช่น `analytics_storage`, `ad_storage`, `ad_user_data`, `ad_personalization`, `functionality_storage`, `personalization_storage`, `security_storage`
- Google tag โหลดแค่ 1 ตัวทั้งเว็บจากการตั้งค่านี้ ไม่ต้องใส่ใน tracking code ของ category (ถ้าใส่ซ้ำ หน้า Setting จะเตือน) tool ที่ไม่ใช่ Google ยังใส่ใน tracking code ของ category ตามเดิม
- ก่อนผู้ใช้ตอบ ระบบส่ง `consent default` เป็น denied ทั้งหมด (category ที่ always-active เป็น granted) พอผู้ใช้ตอบจะส่ง `consent update` และ push event `cookie_consent_update` เข้า `dataLayer` ใช้เป็น Custom Event trigger ใน GTM ได้
- ต้องรัน package upgrade เพื่อเพิ่มคอลัมน์ `consent_mode_types` แล้วเคลียร์ cache
- รายละเอียดการตั้งค่า GTM และขั้นตอนทดสอบดูใน `README.md`

## เรื่องที่ทีมควรรู้ก่อนพัฒนาต่อ

- source of truth ของ consent อยู่ที่ database log ไม่ใช่แค่ cookie ฝั่ง browser
- browser cookies `consent` และ `consent_version` เป็น marker ช่วย frontend แต่ตัว lookup หลักใช้ `consent_id + site_id + consent_version`
- อายุของ consent cookies ปรับได้จากหน้า `/dashboard/cookie_consent/setting` โดยกำหนดได้ `1-400 วัน`
- package จะตั้ง cookie แบบ first-party พร้อม `Path=/`, `SameSite=Lax` และ `Secure` เมื่อหน้าเว็บรันบน HTTPS
- แต่ละ cookie category ระบุรายชื่อ first-party cookies ที่ต้องลบตอน withdraw consent ได้
- รองรับ cookie name แบบ exact และ prefix ด้วย `*` ท้ายคำ เช่น `_ga_*`
- third-party cookies ที่อยู่บน vendor domain อื่น และ HttpOnly cookies ยังลบจาก frontend JavaScript ไม่ได้
- ถ้ามีการแก้ cookie definitions, tracking code หรือ popup copy ระบบอาจสร้าง consent version ใหม่และถามผู้ใช้อีกครั้ง
- `consent_mode_types` นับเป็นส่วนของ consent version เฉพาะตอนที่ตั้งค่าไว้ ดังนั้นก่อนตั้งค่า version เดิมของผู้ใช้จะไม่เปลี่ยน
- class ฝั่ง popup ใช้ BEM ภายใต้ `kos-cookie-popup__*` และมี class ปุ่มแบบ semantic เพิ่มไว้สำหรับ override CSS ได้ง่าย
- ถ้าจะแก้ field ใหม่ใน popup content ต้องแก้ทั้ง schema, dashboard form, save logic, service mapping และ frontend render ให้ครบ

## สถานะปัจจุบัน

- package version ปัจจุบัน: `0.3.0` (เพิ่ม Google Consent Mode V2)
- โครงปัจจุบันพร้อมให้ทีมต่อยอดทั้งด้าน UI, compliance flow, export และ dashboard management
