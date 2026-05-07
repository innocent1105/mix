from sklearn.linear_model import LinearRegression
from sklearn.datasets import make_regression
import matplotlib.pyplot as plt
import pandas as pd
import joblib

X = [10,20,30,40,50]
y = []

def y_values():
    increment = 1
    for i in range(len(X)):
        y.append(increment)
        increment += 1
y_values()   
y = [y]

time_series = [X]
model = LinearRegression()
model.fit(time_series, y)

values = [time_series, y]
print(values)

# print(x_values)

# plot
# plt.plot(X, y, label="Regression", color="red")
# plt.title('Regression')
# plt.xlabel('Date')
# plt.ylabel('Value')
# plt.legend()
# plt.show()


# save the model
# joblib.dump(model, 'linear_regression_model.pkl')
print("model saved")