# Simple Database in PHP7 based on [Sw-Fw-Less](http://github.com/luoxiaojun1992/sw-fw-less)

## Quick Start

### RoyKV

```shell
# PD
java -jar target/roykv-1.0-SNAPSHOT.jar pd path/to/roykv/src/main/resources/conf/pd_node_1_conf
java -jar target/roykv-1.0-SNAPSHOT.jar pd path/to/roykv/src/main/resources/conf/pd_node_2_conf
java -jar target/roykv-1.0-SNAPSHOT.jar pd path/to/roykv/src/main/resources/conf/pd_node_3_conf

# RheaKV
java -jar target/roykv-1.0-SNAPSHOT.jar kv path/to/roykv/src/main/resources/conf/rheakv_node_1_conf 50053
java -jar target/roykv-1.0-SNAPSHOT.jar kv path/to/roykv/src/main/resources/conf/rheakv_node_2_conf 50056
java -jar target/roykv-1.0-SNAPSHOT.jar kv path/to/roykv/src/main/resources/conf/rheakv_node_3_conf 50057
```

### TiKV

```shell
go run main.go
```

## Roadmap

1. Add column name mapping for online DDL
