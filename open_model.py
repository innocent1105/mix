# from sklearn.linear_model import LinearRegression
# from sklearn.datasets import make_regression
# import matplotlib as plt
import joblib

loaded_model = joblib.load('linear_regression_model.pkl')

sample_data = [[1.5]]
prediction = loaded_model.predict(sample_data)




print(prediction)
# print(make_regression(n_samples=100, n_features=1, noise=0))