# DroidCare (Back-end)
## Description
For CZ2006 Software Engineering project at Nanyang Technological University (A.Y. 2014-2015 semester 2).

Front-end Android app can be found at [DroidCare](https://github.com/DroidCare/DroidCare)

## Dependency (via Composer)
* [klein.php](https://github.com/chriso/klein.php)
* [PHPMailer](https://github.com/PHPMailer/PHPMailer)

## Installation
0. Assumptions: PHP, Apache, MySQL have been set up.
1. Clone (download) this project
2. On the project's folder, run `php composer.phar update`
3. Set-up database: run the SQL provided `dc.sql` OR import `dc.sql` to phpmyadmin.
4. Done: access via REST API to `http://localhost/[PATH_TO_YOUR_PROJECT]/`
  - Example: Login via `POST http://localhost/[PATH_TO_YOUR_PROJECT]/user/login`

## REST API Documentation
All return values are in JSON string format, with two keys:
* `status`: `0` on success, `-1` otherwise
* `message`: contains the error/success messages or data requested

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
| `gender`          | `string` | "`male`" or "`female`"
| `passport_number` | `string` | User's passport number
| `nationality`     | `string` | User's nationality
| `date_of_birth`   | `string` | Date of Birth, in `YYYY-MM-DD` format.
| `notification`    | `string` | Notification preference: "`email`", "`sms`", or "`all`"

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
  * `gender`
  * `passport_number`
  * `nationality`
  * `date_of_birth`
  * `notification`
  * `type`

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
| `gender`          | `string` | "`Male`" or "`Female`"
| `passport_number` | `string` | User's passport number
| `nationality`     | `string` | User's nationality
| `date_of_birth`   | `string` | Date of Birth, in `YYYY-MM-DD` format.
| `notification`    | `string` | Notification preference: "`email`", "`sms`", or "`all`"
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
* `message`: array of success/error messages

### Get appointment's attachment
```
GET /appointment/attachment/[i:id]
```

#### Parameters
| Name              | Type   | Description
| ----------------- | ------ | -----------
| `id`              | `integer` | Appointment id.

#### Return
* On success, image stream; **NOTE:** there is no usual status/message return.
* On error
  * `status`: -1
  * `message`: array of error/success messages

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
| `session_id`      | `string` | Session id, returned from `/user/login`

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error/success messages

### Get consultant's available time slot
```
GET appointment/timeslot/[i:user_id]/[s:date]
```

#### Parameters
* `user_id` [integer]: user id, should be of 'consultant' 
* `date` [string]: in YYYY-MM-DD format (Y-m-d)

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of error messages; if success, array of date_time representing **time start** of consultant's **AVAILABLE** time slot.

### Get list of consultant
```
GET /user/consultant
```

#### Parameters
none

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
* `email`

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error/success messages

### Reset password
```
GET /user/reset/[s:password_token]
```
Users are expected to access this URL from e-mail sent by `/user/forget`

#### Parameters
* `password_token`: Generated from `/user/forget`

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error/success messages
