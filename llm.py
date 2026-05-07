import os
from pathlib import Path

# Get the Hugging Face cache path
cache_path = Path(os.getenv("HF_HOME", os.path.expanduser("~/.cache/huggingface")))

# List all subdirectories (models) in the cache
model_directories = [d for d in cache_path.glob("transformers/*") if d.is_dir()]

# Print the names of installed models
for model in model_directories:
    print(model.name)
