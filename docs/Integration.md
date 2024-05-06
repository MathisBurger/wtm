# Integration

This section explains how the application can be used by the employees 
to monitor and manage their worktime. 

## API calls for check in/out

**NOTE:** The following API calls are returning a simple HTML page which can be displayed in the browser.

If you want to check in into the application you will have to use following API call.

```http request
http://<host>/api/v1/check-in/<username>
```

If you want to check out from the application you will have to use following API call.

```http request
http://<host>/api/v1/check-out/<username>
```