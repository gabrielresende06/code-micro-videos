FROM mysql:5.7

COPY ./my.cnf /etc/mysql/my.cnf
COPY ./mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf
RUN usermod -u 1000 mysql
