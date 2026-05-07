from meta_ai_api import MetaAI
import json 


ai = MetaAI()
# data = data = {
#     'message': "The weather in San Francisco today is mostly sunny with a high of 64°F and a low of 53°F. Currently, it's 58°F with passing clouds and a gentle breeze of 17 mph from the north ¹. As for the date, today is Tuesday, January 7, 2025.\n",
#     'sources': [{'link': 'https://www.timeanddate.com/weather/usa/san-francisco', 'title': 'Weather for San Francisco, California, USA - Time and Date'}],
#     'media': []
# }

message = "create a sort algorthm in python"  # prompt comes here
response = ai.prompt(message)

response = response['message']
print(response)
 