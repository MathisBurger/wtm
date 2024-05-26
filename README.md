# Worktime

[![](https://tokei.rs/b1/github/MathisBurger/wtm?category=lines)](https://github.com/XAMPPRocky/tokei).

This project is specifically built for a single company with the specific purpose of allowing the employees to track their 
worktimes and manage holidays and illness. Furthermore, it provides the functionality to generate a PDF file that sums up the overtime
and general worktime for each employee. It can also handle overtime transfer as well as overtime reduction days. Furthermore, the application is able to handle 
users from LDAP interfaces and provide admin permissions based on the LDAP group of a user. in Addition, the application can be updated without any server access only through the web interface

## Licensing

This application is licensed as public source. This means the source code is publicly available but the application can only 
be used by people who have my direct permission. It is strongly individual, so it might not match any user requirements.
If you are interested in using this application for yourself please contact me for further information.

## Installation 

This application can be installed as any other symfony application. There is no extra configuration required except from
the environment configuration that is described later in the README.

## Development

The development and code of this project is currently freezed, because it already matches all requirements that were described by the
client. Therefore, this application will only be developed further if bugs occur, or the requirements are changing.

## Configuration

The following table describes all `.env` variables that need to be configured.

| Variable               | Description                                       |
|------------------------|---------------------------------------------------|
| `DATABASE_URL`         | The URL to the database (MySQL or PostgreSQL)     |
| `IS_DOCKER`            | If the application is in a docker container       |
| `LDAP_BASE_DN`         | The LDAP base DN for searching                    |
| `LDAP_SEARCH_DN`       | The DN to the searching LDAP user                 |
| `LDAP_SEARCH_PASSWORD` | The password to search LDAP with the search user  |
| `LDAP_HOST`            | The host of the LDAP server                       |
| `LDAP_ADMIN_GROUP`     | The CN of the group that should have admin access |
| `LDAP_IT_GROUP`        | The CN of the group that should have IT access    |

## Code quality

The code quality of this project is bad, because it needed to be done quick and there are no major changes required in the future
which is why this code will not be maintained in the future. Therefore, good code quality is not as important as it is normally. This is why
this project has such a bad code quality. Many of the calculation algorithms could be refactored, but this would take a lot of time and is not worth it, because
these algorithms will not change in the future anyway. So please do not judge me for my bad code. Usually it is way better ;)

