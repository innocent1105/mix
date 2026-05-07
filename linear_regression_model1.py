import matplotlib.pyplot as plt
import pandas as pd
import numpy as np
from sklearn.linear_model import LinearRegression

# Generate linear data with noise
x = np.linspace(1, 100, 100) 
noise = np.random.normal(0, 10, 100)  # Random noise to add variability
y = 5 * x + 20 + noise  # Linear relationship with some noise

# Create a DataFrame
df = pd.DataFrame({
    'x': x,
    'y': y
})

x = df[['x']]  # Feature (independent variable)
y = df[['y']]  # Target (dependent variable)

# Fit linear regression model
lr = LinearRegression()
lr.fit(x, y)

# Predict using the fitted model
predictions = lr.predict(x)

# Display model coefficients and intercept
print(f"Linear Regression Model: Coefficient = {lr.coef_[0][0]}, Intercept = {lr.intercept_[0]}")
# Display the first few predictions
print(f"First 5 predictions: {predictions[:5].flatten()}")

# Plot the data and the regression line
plt.scatter(x, y, label="Data", color="blue", alpha=0.5)
plt.plot(x, predictions, label="Regression Line", color="red")
plt.xlabel("x")
plt.ylabel("y")
plt.title("Linear Regression with Noise")
plt.legend()
plt.show()
