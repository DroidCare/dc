## URL
https://dc-kenrick95.rhcloud.com/

## Register
```
POST /user/register
```

### Params
* email
* password
* full_name
* address
* gender
* passport_number
* nationality
* date_of_birth

### Return (JSON):
* status: 0 on success, -1 otherwise
* message: array of success/error messages

## Login
```
POST /user/login
```

### Params:
* email
* password

### Return (JSON):
* status: 0 on success, -1 otherwise
* message: array of success/error messages