### Configured Alerts:
- **🚨 Critical**: Pod crashes, restarts → #webhook-test
- **⚠️ Warning**: Pod not ready, replica mismatches → #webhook-test
- **📢 Default**: General alerts → #webhook-test

### Alert Types:
1. **PodCrashLooping** - When pods restart (Critical)
2. **PodNotReady** - When pods stay not ready >5min (Warning)  
3. **DeploymentReplicasMismatch** - When replicas don't match (Warning)

---

# Create namespace
kubectl create namespace monitoring --dry-run=client -o yaml | kubectl apply -f -

# Add Helm repository
helm repo add prometheus-community https://prometheus-community.github.io/helm-charts
helm repo update


# Add EBS CSI Drive

eksctl create addon --name aws-ebs-csi-driver --cluster ekswithavinash --force

# Deploy monitoring stack
helm upgrade --install prometheus-stack prometheus-community/kube-prometheus-stack \
  --namespace monitoring \
  --values values.yaml

# Wait for pods to be ready
echo "⏳ Waiting for Prometheus to be ready..."
kubectl wait --for=condition=ready pod -l app.kubernetes.io/name=prometheus -n monitoring --timeout=300s

# Apply custom alerts
kubectl apply -f k8s-alerts.yaml


=======


# Create test namespace
kubectl create namespace alert-test --dry-run=client -o yaml | kubectl apply -f -

# Creating crash loop pod (will trigger critical alert)...
kubectl run crash-pod --image=busybox --restart=Always -n alert-test -- sh -c "exit 1"

# Creating deployment with replica mismatch...
kubectl create deployment test-app --image=nginx --replicas=3 -n alert-test
kubectl patch deployment test-app -n alert-test -p '{"spec":{"template":{"spec":{"containers":[{"name":"nginx","image":"nginx:invalid-tag"}]}}}}'

# Creating normal deployment...
kubectl create deployment working-app --image=nginx --replicas=2 -n alert-test

# Creating service...
kubectl expose deployment working-app --port=80 --name=test-service -n alert-test

=====


