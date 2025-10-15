You can use Visual Studio Code to run the notebook by:
- Installing "Jupyter" extension. Ref: https://www.alphr.com/vs-code-open-jupyter-notebook/
- Install the jupyter kernel for bash. Ref: https://pypi.org/project/bash_kernel/
```shell
python3 -m venv .venv
source .venv/bin/activate
pip install --upgrade pip
pip install bash_kernel
python3 -m bash_kernel.install
jupyter kernelspec list
```
