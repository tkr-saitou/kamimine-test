# Makefile
.PHONY: all
program = DecideRosen
objs = DecideRosen.o

# 定義済マクロの再定義
MYSQLLIBDIR = /usr/lib64/mysql
MYSQLINCDIR = /usr/include/mysql

INCLUDE = -I$(MYSQLINCDIR) -I../include

CC = g++
#CFLAGS = -g -m64 $(INCLUDE) --input-charset=cp932
#CFLAGS = -m64 $(INCLUDE) --input-charset=cp932
CFLAGS = -m64 $(INCLUDE) --input-charset=UTF-8
LDFLAGS = -lm -L $(MYSQLLIBDIR) -lmysqlclient



.SUFFIXES: .cpp .o

$(program): $(objs)
	$(CC) -o $(program).exe $^ $(LDFLAGS)

.cpp.o:
	$(CC) $(CFLAGS) -c $<


.PHONY: clean
clean:
	$(RM) $(program).exe ${objs}
