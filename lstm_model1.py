import numpy as np
import pandas as pd
import matplotlib.pyplot as plt
from sklearn.preprocessing import MinMaxScaler
from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import LSTM, Dense

# Load your dataset
# Example: Create a dummy dataset for demonstration
# Replace this with loading your actual data
data = np.sin(np.linspace(0, 100, 200))  # Sine wave data for demonstration
df = pd.DataFrame(data, columns=["Value"])

# Scale the data
scaler = MinMaxScaler(feature_range=(0, 1))
scaled_data = scaler.fit_transform(df)

# Split data into train and test
train_size = int(len(scaled_data) * 0.8)
train_data, test_data = scaled_data[:train_size], scaled_data[train_size:]

# Function to create sequences of data for LSTM
def create_dataset(dataset, time_step=1):
    X, y = [], []
    for i in range(len(dataset) - time_step - 1):
        X.append(dataset[i:(i + time_step), 0])
        y.append(dataset[i + time_step, 0])
    return np.array(X), np.array(y)

# Prepare data with time_step (window size)
time_step = 10
X_train, y_train = create_dataset(train_data, time_step)
X_test, y_test = create_dataset(test_data, time_step)

# Reshape the data to fit LSTM input format (samples, time steps, features)
X_train = X_train.reshape(X_train.shape[0], X_train.shape[1], 1)
X_test = X_test.reshape(X_test.shape[0], X_test.shape[1], 1)

# Build the LSTM model
model = Sequential()
model.add(LSTM(units=50, return_sequences=False, input_shape=(time_step, 1)))
model.add(Dense(units=1))

# Compile and train the model
model.compile(optimizer='adam', loss='mean_squared_error')
history = model.fit(X_train, y_train, epochs=100, batch_size=32, verbose=1)

# Predicting on train and test data
train_predict = model.predict(X_train)
test_predict = model.predict(X_test)

# Inverse transform the predictions and actual values to the original scale
train_predict = scaler.inverse_transform(train_predict)
y_train_actual = scaler.inverse_transform([y_train])
test_predict = scaler.inverse_transform(test_predict)
y_test_actual = scaler.inverse_transform([y_test])

# Plot the results
plt.figure(figsize=(12, 6))

# Plot training data and predictions
plt.plot(df.index[:len(y_train_actual[0])], y_train_actual[0], label='Training Data', color='blue')
plt.plot(df.index[time_step:len(train_predict) + time_step], train_predict.flatten(), label='Train Prediction', color='green')

# Plot testing data and predictions
plt.plot(df.index[len(train_predict) + time_step + 1:], y_test_actual[0], label='Testing Data', color='red')
plt.plot(df.index[len(train_predict) + (time_step * 2) + 1:], test_predict.flatten(), label='Test Prediction', color='orange')

plt.legend()
plt.xlabel('Time')
plt.ylabel('Value')
plt.title('LSTM Model Predictions')
plt.show()
