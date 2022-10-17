import requests
import json
import threading

url = "http://127.0.0.1:8000/api/players/1/useItemRaw"
data = {
    "item_id": 2,
    "count": 1
}


def fun():
    r = requests.post(url, json=data)
    print(r.json())


for i in range(100):
    t = threading.Thread(target=fun)
    t.start()
    t.join()
