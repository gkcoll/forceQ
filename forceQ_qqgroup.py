'''
Author: @灰尘疾客
File: AutoJoinQGroup.py
Date: 2023/01/09
Description: Recieved jump & join QQ group(s) without IDKEY.
More Info:
- 此代码为原创，网上截至今日暂无出现相关案例的 Python 代码。
- Reference: https://blog.csdn.net/weixin_43272781/article/details/104380379
'''
import requests
import time
import webbrowser

def get_data(group_list):
    query_api = "https://qun.qq.com/proxy/domain/shang.qq.com/wpa/g_wpa_get"
    t = str(int(time.time()*1000))  # The needed time stamp includes three numbers after the decimal point then remove the decimal point(also can understand as multiply by 1000 then get integer part only.)

    # Handle elements in list into needed format: Turn into string and use English comma in url encoding to joint in.
    # group_list = [824697580]
    group_list = [str(i) for i in group_list]
    groups = "%2C".join(group_list)

    headers = {"referer": "https://qun.qq.com/proxy.html?callback=1&id=1"}  # Headers info(the referen parameter is necessary.)
    query_url = query_api + "?guin=" + groups + "&t=" + t  # Joint query links


    resp = requests.get(query_url, headers=headers)
    data = resp.json()  # Get returned data(json)

    return data['result']['data']

def main(*args):
    # group_list = [824697580]
    data = get_data(args)

    # Jump links in sequence
    for i in range(len(args)):
        group_url = "http://shang.qq.com/wpa/qunwpa?idkey="+data[i]['key']
        webbrowser.open(group_url)

if __name__ == "__main__":
    main(824697580)