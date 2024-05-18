#!/bin/bash

if [ $# -eq 0 ]; then
  echo "輸入要執行的動作(start/restart/down)"
  exit 1
fi

sudo chmod 764 ./deployment/entrypoint.sh 

action="$1"

if [ "$action" == "start" ]; then
  # 启动 Docker Compose 服务
  docker-compose -f ./deployment/docker-compose.yml up -d 
  echo "啟動服務"
elif [ "$action" == "down" ]; then
  # 停止 Docker Compose 服务
  docker-compose -f ./deployment/docker-compose.yml down
  echo "停止服務"
elif [ "$action" == "restart" ]; then
  # 停止 Docker Compose 服务
  docker-compose -f ./deployment/docker-compose.yml restart
  echo "重啟服務"
else
  echo "請確認輸入的指令是否正確"
  exit 1
fi
