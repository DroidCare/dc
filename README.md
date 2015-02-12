# DroidCare (Back-end)
## Description
For CZ2006 Software Engineering project at Nanyang Technological University (A.Y. 2014-2015 semester 2).

## Dependency
* [klein.php](https://github.com/chriso/klein.php)

## REST API Documentation
All return values are in JSON string format.

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
* email
* password
* full_name
* address
* gender
* passport_number
* nationality
* date_of_birth

#### Return
* status: 0 on success, -1 otherwise
* message: array of success/error messages

### Login
```
POST /user/login
```

#### Parameters
* email
* password

#### Return
* status: 0 on success, -1 otherwise
* message: array of success/error messages

### View User Details
```
GET /user/[i:id]
```

#### Parameters
* id [integer]

#### Return
* status: 0 on success, -1 otherwise
* message: array of error messages; or object containing the data:
** email
** full_name
** address
** gender
** passport_number
** nationality
** date_of_birth