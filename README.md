# DroidCare (Back-end)
## Description
For CZ2006 Software Engineering project at Nanyang Technological University (A.Y. 2014-2015 semester 2).

Front-end Android app can be found at [DroidCare](https://github.com/edocsss/DroidCare)

## Dependency
* [klein.php](https://github.com/chriso/klein.php)

## REST API Documentation
All return values are in JSON string format, with two keys:
* `status`: `0` on success, `-1` otherwise
* `message`: contains the error/success messages or data requested

### Request URL
```
https://dc.kenrick95.org/
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
* `email`
* `password`
* `full_name`
* `address`
* `gender`
* `passport_number`
* `nationality`
* `date_of_birth` (YYYY-MM-DD)

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of success/error messages

### Login
```
POST /user/login
```

#### Parameters
* `email`
* `password`

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of error messages; or `session_id` on success, please save this `session_id` locally as it will be used for authentication for other method.

### View User Details
```
POST /user/[i:id]
POST /user/
```

#### Parameters
* `id` [integer]  (optional; if not set, user id used is from the session, i.e. current logged in user)
  * **Note**: If `id` is not set, URL must end with "/".
* `session_id`

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of error messages; or object containing the data:
  * `email`
  * `full_name`
  * `address`
  * `gender`
  * `passport_number`
  * `nationality`
  * `date_of_birth`
  * `type`

### Update User Details
```
POST /user/update
```

#### Parameters
* `id` [integer]
* `password`
* `full_name`
* `address`
* `gender`
* `passport_number`
* `nationality`
* `date_of_birth`
* `session_id`, returned at login

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of error/success messages

### Logout
```
POST /user/logout
```

#### Parameters
* `session_id`, returned at login

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of success/error messages

### Make new appointment
```
POST /appointment/new
```

#### Parameters
* `patient_id`
* `consultant_id`
* `date_time` (YYYY-MM-DD HH:mm:SS; Y-M-D H:i:s)
* `health_issue`
* `attachment` [file: png, jpg, gif], stored as `attachment_paths`: for 'follow-up' type; uploaded image inaccessible directly, must be routed via API [todo]
* `type`: 'follow-up', 'referral'
* `referrer_name`: may NULL if `type` is not 'referral'
* `referrer_clinic`: may NULL if `type` is not 'referral'
* `previous_id`: may NULL if `type` is not 'follow-up'
* `remarks`
* `session_id`: returned at login

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of success/error messages

### Get appointment's attachment
```
GET /appointment/attachment/[s:attachment_id]
```

#### Parameters
* `attachment_name` from `attachment_paths`

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of error messages; if success, base-64 encoded string of the image file

### Update Appointment Status (by Consultant)
```
POST appointment/status
```

#### Parameters
* `id`: appointment_id
* `status`: new status ('pending', 'accepted', 'rejected', 'finished')
* `remarks`
* `session_id`: returned at login

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of success/error messages

### Get Details of Appointment
```
POST /appointment/[i:id]
```

#### Parameters
* `id` [integer]: appointment id
* `session_id`: returned at login

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error messages; or object containing the data:
  * `patient_id` 
  * `consultant_id` 
  * `date_time` 
  * `health_issue` 
  * `attachment_paths` 
  * `type` 
  * `referrer_name` 
  * `referrer_clinic` 
  * `previous_id` 
  * `remarks`
  * `status`

### Get Details of Appointment
```
POST /appointment/user/[i:id]
POST /appointment/user
```
List down appointments:
* created by user (if user type is 'patient') OR
* assigned to user (if user type is 'consultant') OR
* of all users (if user_type is 'admin').

#### Parameters
* `id` [integer]: user id  (optional; if not set, user id used is from the session, i.e. current logged in user)
* `session_id`: returned at login

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error messages; or array of objects containing the data:
  * `patient_id` 
  * `consultant_id` 
  * `date_time` 
  * `health_issue` 
  * `attachment_paths` 
  * `type` 
  * `referrer_name` 
  * `referrer_clinic` 
  * `previous_id` 
  * `remarks`
  * `status`