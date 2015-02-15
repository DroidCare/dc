# DroidCare (Back-end)
## Description
For CZ2006 Software Engineering project at Nanyang Technological University (A.Y. 2014-2015 semester 2).

Front-end Android app can be found at [DroidCare](https://github.com/edocsss/DroidCare)

## Dependency
* [klein.php](https://github.com/chriso/klein.php)

## REST API Documentation
All return values are in JSON string format, with two keys:
* `status`: 1 on success, -1 otherwise
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
* `status`: 0 on success, -1 otherwise
* `message`: array of success/error messages

### Login
```
POST /user/login
```

#### Parameters
* `email`
* `password`

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error messages; or `session_id` on success, please save this `session_id` locally as it will be used for authentication for other method.

### View User Details
```
POST /user/[i:id]
```

#### Parameters
* `id` [integer]
* `session_id`

#### Return
* `status`: 0 on success, -1 otherwise
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
* `status`: 0 on success, -1 otherwise
* `message`: array of error/success messages

### Logout
```
POST /user/logout
```

#### Parameters
* `session_id`, returned at login

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of success/error messages