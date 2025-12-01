Quay: 

podman login quay.io
colintkn


# Steps to push image
podman build -t quay.io/colintkn/vault-php-app . --arch amd64
podman push quay.io/colintkn/vault-php-app

## If you need to rename image
podman images 
podman tag php:vault quay.io/colintkn/vault-php-app:latest
podman push quay.io/colintkn/vault-php-app

https://notes.kodekloud.com/docs/DevSecOps-Kubernetes-DevOps-Security/HashiCorp-Vault-Kubernetes/Demo-Vault-PHP-Application 


kubectl apply -f php-app-k8s-deploy.yaml

How to access the application: 
http://<Public Load Balancer IP>:<node-port>/
http://169.38.126.121:30646

Vault IP: 
http://162.133.140.29:8200/ui/vault/auth

### Configuring Vault 
> export VAULT_ADDR=http://162.133.140.29:8200
> vault login 
 
# Enable the KV Secrets Engine 
export KVPATH=kv-vso-demo
vault secrets disable $KVPATH
vault secrets enable -path $KVPATH kv

vault kv put kv-vso-demo/app-secret \
    username="user123" \
    password="Passw0rd" \
    apiKey="12345678"

# Configuring the Authentication to vault
export KUBE_EXT_API="https://api.68b1524bd4aed54704f69cc0.ap1.techzone.ibm.com:6443"
export KUBENAMESPACE=vault-secrets-operator
export KUBESVCACCOUNTTOKEN=vaultauth-sa-token
export KUBESVCACCOUNT=vaultauth-sa
# Name of the K8s service account token used for verification when Vault connects to minikube for K8s JWT auth
# This is required as Vault is external from the K8s
export KUBESVCACCOUNTTOKEN=vaultauth-sa-token

kubectl create -f - <<EOF
apiVersion: v1
kind: ServiceAccount
metadata:
  name: $KUBESVCACCOUNT
  namespace: $KUBENAMESPACE
EOF

kubectl create -f - <<EOF
apiVersion: v1
kind: Secret
metadata:
  name: $KUBESVCACCOUNTTOKEN
  namespace: $KUBENAMESPACE
  annotations:
    kubernetes.io/service-account.name: $KUBESVCACCOUNT
type: kubernetes.io/service-account-token
EOF

kubectl create -f - <<EOF
apiVersion: rbac.authorization.k8s.io/v1
kind: ClusterRoleBinding
metadata:
  name: role-tokenreview-binding
  namespace: $KUBENAMESPACE
roleRef:
  apiGroup: rbac.authorization.k8s.io
  kind: ClusterRole
  name: system:auth-delegator
subjects:
  - kind: ServiceAccount
    name: $KUBESVCACCOUNT
    namespace: $KUBENAMESPACE
EOF

# Role name to be used by openshift to read dynamic secrets
export K8SROLE=kv-auth-role-openshift

# Enable the Kubernetes auth method 
export K8SAUTHPATH=openshift
vault auth disable $K8SAUTHPATH
vault auth enable -path $K8SAUTHPATH kubernetes 
export JWT_TOKEN_DEFAULT_DEMONS=$(kubectl get secret -n $KUBENAMESPACE $KUBESVCACCOUNTTOKEN --output='go-template={{ .data.token }}' | base64 --decode)
export KUBE_CA_CERT=$(kubectl get configmap kube-root-ca.crt -n default -o jsonpath='{.data.ca\.crt}')


vault write auth/$K8SAUTHPATH/config \
    token_reviewer_jwt="$JWT_TOKEN_DEFAULT_DEMONS" \
    kubernetes_host="$KUBE_EXT_API" \
    kubernetes_ca_cert="$KUBE_CA_CERT"

vault read auth/$K8SAUTHPATH/config

vault write auth/$K8SAUTHPATH/login role="$K8SROLE" jwt=$JWT_TOKEN_DEFAULT_DEMONS


# Setting the Authentication for Application 
vault policy write policy-kv-readonly - <<EOF
path "kv-vso-demo/*" {
  capabilities = ["read", "list"]
}
EOF

export KUBEAPPSVCACCOUNT=app
export KUBEAPPNAMESPACE=default

vault write auth/$K8SAUTHPATH/role/$K8SROLE \
   bound_service_account_names=$KUBEAPPSVCACCOUNT \
   bound_service_account_namespaces=$KUBEAPPNAMESPACE \
   period=60 \
   token_policies=policy-kv-readonly


# set up CRDs 
kubectl create -f - <<EOF
apiVersion: secrets.hashicorp.com/v1beta1
kind: VaultConnection
metadata:
  namespace: vault-secrets-operator
  name: vault-connection
spec:
  # required configuration
  # address to the Vault server.
  address: http://162.133.140.29:8200
  skipTLSVerify: true
EOF

### Getting the CA information
kubectl get configmap kube-root-ca.crt -n default

### Installing VSO 
https://developer.hashicorp.com/vault/tutorials/kubernetes-introduction/vault-secrets-operator?productSlug=vault&tutorialSlug=kubernetes&tutorialSlug=vault-secrets-operator

Install the Operator via the Openshfit Operator 

export VAULTADDRESS=http://162.133.140.29:8200

echo "K8s namespace: $KUBENAMESPACE"
echo "Vault Server Address: $VAULTADDRESS"

kubectl create -f - <<EOF
apiVersion: secrets.hashicorp.com/v1beta1
kind: VaultConnection
metadata:
  name: vault-connection
  namespace: $KUBEAPPNAMESPACE
spec:
  address: $VAULTADDRESS
  skipTLSVerify: true
EOF

echo "K8s namespace: $KUBENAMESPACE"
echo "K8s Auth Path: $K8SAUTHPATH"
echo "K8s role: $K8SROLE"
echo "K8s Service Account: $KUBESVCACCOUNT"

### Optional 
kubectl create -f - <<EOF
---
apiVersion: secrets.hashicorp.com/v1beta1
kind: VaultAuthGlobal
metadata:
  namespace: vault-secrets-operator
  name: vault-auth-global
spec:
  defaultAuthMethod: kubernetes
  kubernetes:
    audiences:
    - vault
    mount: openshift 
    namespace: vault-secrets-operator
    role: default
    serviceAccount: vaultauth-sa
    tokenExpirationSeconds: 600


# Create the VaultStaticSecret CRD.  
# This will create the K8s secret and bind it to sync with the key value secret.
# Other supported options are VaultStaticSecret and VaultPKISecret
# Ref: https://developer.hashicorp.com/vault/docs/platform/k8s/vso/sources/vault#vault-secret-custom-resource-definitions---
kubectl create -f - <<EOF
apiVersion: secrets.hashicorp.com/v1beta1
kind: VaultStaticSecret
metadata:
  namespace: $KUBEAPPNAMESPACE
  name: vault-static-secret
spec:
  vaultAuthRef: vault-auth-kv-app
  mount: kvv2
  type: kv-v2
  path: kv-vso-demo/app-secret
  version: 2
  refreshAfter: 60s
  destination:
    create: true
    name: kv-app-secret
EOF



For debugging authentication issues: 
