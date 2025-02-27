#!/bin/bash

MAC="$1"
BROADCAST="192.168.1.255"
PORT=9

wakeonlan -i $BROADCAST $MAC
