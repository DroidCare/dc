# DroidCare (Back-end)
## Description
For CZ2006 Software Engineering project at Nanyang Technological University (A.Y. 2014-2015 semester 2).

Front-end Android app can be found at [DroidCare](https://github.com/DroidCare/DroidCare)

## Dependencies (via Composer)
* [klein.php](https://github.com/chriso/klein.php)
* [PHPMailer](https://github.com/PHPMailer/PHPMailer)

## Local Installation
0. Assumptions: PHP, Apache, MySQL have been set up.
1. Clone (download) this project
2. On the project's folder, run `php composer.phar update`
3. Set-up database: run the SQL provided `dc.sql` OR import `dc.sql` to phpmyadmin.
4. Change configurations at `config.php` (especially database configuration)
5. Done: access via REST API to `http://localhost/[PATH_TO_YOUR_PROJECT]/`
  - Example: Login via `POST http://localhost/[PATH_TO_YOUR_PROJECT]/user/login`

## REST API Documentation
All return values are in JSON string format, with two keys:
* `status`: `0` on success, `-1` otherwise
* `message`: contains the error/success messages or data requested
**Note**: "`REQUEST`"-type request accepts both `POST` and `GET`.

### Request URL
```
http://dc.kenrick95.org/
```
or
```
https://dc-kenrick95.rhcloud.com/
```

### Register
```
POST /user/register
```

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `email`           | `string` | User's email, should be a valid email, checked by PHP's [filter_var](http://php.net/manual/en/function.filter-var.php)
| `password`        | `string` | User's password, 6-32 character long
| `full_name`       | `string` | User's full name
| `address`         | `string` | User's address
| `phone_number`    | `string` | User's phone_number
| `passport_number` | `string` | User's passport number
| `gender`          | `string` | "`male`" or "`female`"
| `nationality`     | `string` | User's nationality
| `date_of_birth`   | `string` | Date of Birth, in `YYYY-MM-DD` format.
| `notification`    | `string` | Notification preference: "`local`", "`email`", "`sms`", or "`all`"
| `location`        | `string` | User's country location

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of success/error messages

### Login
```
POST /user/login
```

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `email`           | `string` | User's email, should be a valid email, checked by PHP's [filter_var](http://php.net/manual/en/function.filter-var.php)
| `password`        | `string` | User's password

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of error messages; or `session_id` on success, please save this `session_id` locally as it will be used for authentication for other method.

### View User Details
```
POST /user/[i:id]
POST /user/
```

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `id`              | `integer` | (Optional) User id. If not set, user id used is from the session, i.e. current logged in user. **Note**: If `id` is not set, URL must end with "`/`".
| `session_id`      | `string` | Session id, returned from `/user/login`

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of error messages; or object containing the data:
  * `id`: user id
  * `email`
  * `full_name`
  * `address`
  * `phone_number`
  * `gender`: 'male', or 'female'
  * `passport_number`
  * `nationality`
  * `date_of_birth`
  * `notification`: 'local', 'email', 'sms', or 'all'
  * `location`: country
  * `specialization`: not empty if consultant
  * `type`: 'patient', 'admin', or 'consultant'

### Update User Details
```
POST /user/update
```

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `id`              | `integer` | User id.
| `email`           | `string` | User's email, should be a valid email, checked by PHP's [filter_var](http://php.net/manual/en/function.filter-var.php)
| `password`        | `string` | User's password, 6-32 character long
| `full_name`       | `string` | User's full name
| `address`         | `string` | User's address
| `phone_number`    | `string` | User's phone_number
| `passport_number` | `string` | User's passport number
| `gender`          | `string` | "`Male`" or "`Female`"
| `nationality`     | `string` | User's nationality
| `date_of_birth`   | `string` | Date of Birth, in `YYYY-MM-DD` format.
| `notification`    | `string` | Notification preference: "`local`", "`email`", "`sms`", or "`all`"
| `location`        | `string` | User's country location
| `session_id`      | `string` | Session id, returned from `/user/login`

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of error/success messages

### Logout
```
POST /user/logout
```

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `session_id`      | `string` | Session id, returned from `/user/login`

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of success/error messages

### Make new appointment
```
POST /appointment/new
```

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `patient_id`      | `integer` | (Optional) User id of type 'patient'. If not set, user id used is from the session, i.e. current logged in user. 
| `consultant_id`   | `integer` | User id of type 'consultant'
| `date_time`       | `string` | YYYY-MM-DD HH:mm:SS; `Y-M-D H:i:s`; should be multiple of 30 minutes
| `health_issue`    | `string` | Text describing the health issue
| `attachment`      | `string` | Base64-encoded string of the image, may be `NULL` if `type` is not '`follow-up`'
| `type`            | `string` | '`follow-up`', '`referral`', or '`normal`'
| `referrer_name`   | `string` | may be `NULL` if `type` is not '`referral`'
| `referrer_clinic` | `string` | may be `NULL` if `type` is not '`referral`'
| `previous_id`     | `integer` | may be `NULL` if `type` is not '`follow-up`'
| `session_id`      | `string` | Session id, returned from `/user/login`

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of error messages; OR object containing success message and ID of recently inserted row.

### Get appointment's attachment
```
REQUEST /appointment/attachment/[i:id]
```

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `id`              | `integer` | Appointment id.

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error messages; or base-64 encoded image

### Update Appointment Status (by Consultant)
```
POST appointment/status
```

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `id`              | `integer` | Appointment id.
| `status`          | `string`  | '`pending`', '`accepted`', '`rejected`', or '`finished`'
| `remarks`         | `string`  | (Optional)
| `session_id`      | `string`  | Session id, returned from `/user/login`

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of success/error messages

### Get Details of Appointment
```
POST /appointment/[i:id]
```

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `id`              | `integer` | Appointment id.
| `session_id`      | `string` | Session id, returned from `/user/login`

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error messages; or object containing the data:
  * `patient_id`
  * `patient_name`
  * `consultant_id`
  * `consultant_name`
  * `date_time` 
  * `health_issue` 
  * `attachment`
  * `type` 
  * `referrer_name` 
  * `referrer_clinic` 
  * `previous_id` 
  * `remarks`
  * `status`

### Get List of Appointments
```
POST /appointment/user/[i:id]
POST /appointment/user
```
List down appointments:
* created by user (if user type is 'patient') OR
* assigned to user (if user type is 'consultant') OR
* of all users (if user_type is 'admin').

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `id`              | `integer` | (Optional) User id. If not set, user id used is from the session, i.e. current logged in user.
| `session_id`      | `string` | Session id, returned from `/user/login`

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error messages; or array of objects containing the data:
  * `id`: appointment id
  * `patient_id`
  * `patient_name`
  * `consultant_id`
  * `consultant_name`
  * `date_time`
  * `health_issue`
  * `attachment` (base64-encoded string of the image)
  * `type`
  * `referrer_name`
  * `referrer_clinic` 
  * `previous_id` 
  * `remarks`
  * `status`

### Cancel Appointment (by Patient)
```
POST appointment/cancel
```

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `id`              | `integer` | Appointment id
| `session_id`      | `string` | Session id, returned from `/user/login`

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of success/error messages

### Edit Appointment
```
POST /appointment/edit
```

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `id`              | `integer` | Appointment id
| `patient_id`      | `integer` | (Optional) User id of type 'patient' . If not set, user id used is from the session, i.e. current logged in user.
| `consultant_id`   | `integer` | User id of type 'consultant'
| `date_time`       | `string` | YYYY-MM-DD HH:mm:SS; `Y-M-D H:i:s`
| `health_issue`    | `string` | Text describing the health issue
| `referrer_name`   | `string` | may be `NULL` if `type` is not '`referral`'
| `referrer_clinic` | `string` | may be `NULL` if `type` is not '`referral`'
| `session_id`      | `string` | Session id, returned from `/user/login`

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error/success messages

### Get consultant's available time slot
```
REQUEST appointment/timeslot/[i:user_id]/[s:date]
```

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `user_id`         | `integer` | user id, should be of 'consultant'-type
| `date`            | `string` | YYYY-MM-DD (`Y-m-d`)

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of error messages; if success, array of date_time representing **time start** of consultant's **AVAILABLE** time slot.

### Get list of consultant
```
REQUEST /user/consultant
```

#### Parameters

| Name              | Type   | Description
| ----------------- | ------ | -----------
| `location`        | `string` | (optional) Consultant's country location

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of error messages; or array of objects containing the data:
  * `id`: user id of consultant
  * `full_name`

### Forget password
```
POST /user/forget
```

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `email`           | `string` | user email

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error/success messages

### Reset password
```
GET /user/reset/[s:password_token]
```
Users are expected to access this URL from e-mail sent by `/user/forget`

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `password_token`  | `string` | Generated from `/user/forget` and sent to user's e-mail

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error/success messages

### Notify patient on appointment
```
POST /appointment/notify
```

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `id`              | `integer` | appointment id, which appointment to be notified
| `session_id`      | `string` | Session id, returned from `/user/login`

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error/success messages
