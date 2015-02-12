# DroidCare (Back-end)
## Description
For CZ2006 Software Engineering project at Nanyang Technological University (A.Y. 2014-2015 semester 2).

## Dependency
* [klein.php](https://github.com/chriso/klein.php)

## REST API Documentation

### Request URL
```
https://dc-kenrick95.rhcloud.com/
```

### Register
```
POST /user/register
```

#### Params
* email
* password
* full_name
* address
* gender
* passport_number
* nationality
* date_of_birth

#### Return (JSON):
* status: 0 on success, -1 otherwise
* message: array of success/error messages

### Login
```
POST /user/login
```

#### Params:
* email
* password

#### Return (JSON):
* status: 0 on success, -1 otherwise
* message: array of success/error messages