#!/bin/env python
# encoding: utf-8 #
import os
import sys
import simplejson as json
import datetime
import time
from time import mktime
import MySQLdb

log_dir = "/data2/log/soj"
dw_db = MySQLdb.connect(
    host="192.168.100.30",
    user="zzkdbuser",
    passwd="gwutest",
    db="dw_db",
    port=3306,
    charset='utf8'
)
dw_cursor = dw_db.cursor(cursorclass=MySQLdb.cursors.DictCursor)


def sync_others_log(date_str, log_type):
    if log_type == "register":
        log_filename = "register%s.log"
        table = "zzk_user_register_log"
    elif log_type == "phone_verify":
        log_filename = "phoneVerify%s.log"
        table = "zzk_user_phone_verify_log"
    elif log_type == "code_verify":
        log_filename = "codeVerify%s.log"
        table = "zzk_user_code_verify_log"
    elif log_type == "booking_phone_verify":
        log_filename = "BookingPhoneVerify%s.log"
        table = "zzk_user_booking_phone_verify_log"
    elif log_type == "booking_code_verify":
        log_filename = "BookingCodeVerify%s.log"
        table = "zzk_user_booking_code_verify_log"
    log_filepath = log_dir + "/" + (log_filename % date_str)
    if not os.path.isfile(log_filepath):
        return
    sql_clear = """ DELETE FROM dw_db.""" + table + """ WHERE dates=%s """
    sql_insert = """ INSERT INTO dw_db.""" + table + """ (dates, status, guid, msg, user_msg, data) VALUES (%s, %s, %s, %s, %s, %s) """
    insert_arr = []
    for line in file(log_filepath).read().split("\n"):
        try:
            line = json.loads(line)
        except:
            continue
        if line.has_key('guid'):
            guid = line['guid']
        else:
            guid = None
        if line.has_key('msg'):
            msg = line['msg']
        else:
            msg = None
        if line.has_key('userMsg'):
            user_msg = line['userMsg']
        else:
            user_msg = None
        if line.has_key('data'):
            data = json.dumps(line['data'])
        else:
            data = None
        if log_type in ["booking_code_verify"]:
            status = int(line['code'])
        else:
            status = int(line['status'])
        insert_arr.append((
            date_str,
            status,
            guid,
            msg,
            user_msg,
            data
        ))
    dw_cursor.execute(sql_clear, date_str)
    dw_db.commit()
    dw_cursor.executemany(sql_insert, insert_arr)
    dw_db.commit()

def sync_login_log(date_str):
    log_filename = "login%s.log"
    log_filepath = log_dir + "/" + (log_filename % date_str)
    if not os.path.isfile(log_filepath):
        return
    sql_clear = """ DELETE FROM dw_db.zzk_user_login_log WHERE dates=%s """
    sql_insert = """ INSERT INTO dw_db.zzk_user_login_log (dates, code, guid, code_msg, body) VALUES (%s, %s, %s, %s, %s) """
    insert_arr = []
    for line in file(log_filepath).read().split("\n"):
        try:
            line = json.loads(line)
        except:
            continue
        if line.has_key('guid'):
            guid = line['guid']
        else:
            guid = None
        if line.has_key('codeMsg'):
            code_msg = line['codeMsg']
        else:
            code_msg = None
        if line.has_key('body'):
            body = json.dumps(line['body'])
        else:
            body = None
        insert_arr.append((
            date_str,
            int(line['code']),
            guid,
            code_msg,
            body
        ))
    dw_cursor.execute(sql_clear, date_str)
    dw_db.commit()
    dw_cursor.executemany(sql_insert, insert_arr)
    dw_db.commit()


if __name__ == "__main__":
    if len(sys.argv) < 3:
        date_from = datetime.date.today() - datetime.timedelta(1)
        date_to = date_from + datetime.timedelta(1)
    else:
        # for python 2.7
        date_from = datetime.datetime.strptime(sys.argv[1], "%Y-%m-%d")
        date_to = datetime.datetime.strptime(sys.argv[2], "%Y-%m-%d")

        # for python 2.4
        #date_from = datetime.datetime.fromtimestamp(mktime(time.strptime(sys.argv[1], "%Y-%m-%d")))
        #date_to = datetime.datetime.fromtimestamp(mktime(time.strptime(sys.argv[2], "%Y-%m-%d")))
    d = date_from
    while d < date_to:
        date_str = d.strftime("%Y-%m-%d")
        print date_str
        sync_others_log(date_str, "phone_verify")
        sync_others_log(date_str, "code_verify")
        sync_others_log(date_str, "booking_phone_verify")
        sync_others_log(date_str, "booking_code_verify")
        sync_others_log(date_str, "register")
        sync_login_log(date_str)
        d += datetime.timedelta(1)
