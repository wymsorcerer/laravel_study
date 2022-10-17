import grequests
import json

# url = "http://127.0.0.1:8000/api/players/1/useItemRaw"
url = "http://127.0.0.1:3000/useItem"
data = {
    "item_id": 2,
    "count": 1
}

# req_list = [
#     grequests.post(url, json=data),
#     grequests.post(url, json=data),
#     grequests.post(url, json=data),
#     grequests.post(url, json=data),
#     grequests.post(url, json=data)
# ]

req_list = []
for i in range(100):
	req_list.append(
		grequests.post(url)
	)

res_list = grequests.map(req_list)

for item in res_list:
    print(item.json())
