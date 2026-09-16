<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Page Not Found</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Arial',sans-serif;background:linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;overflow:hidden;}
        .error-container{text-align:center;max-width:800px;width:100%;padding:2rem;}
        .stop-sign{width:200px;height:200px;background:#dc3545;margin:0 auto 2rem;position:relative;transform:rotate(22.5deg);border-radius:20px;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 30px rgba(220,53,69,0.3);animation:bounce 2s infinite;}
        .stop-sign::before{content:'';position:absolute;top:-10px;left:-10px;right:-10px;bottom:-10px;background:#dc3545;border-radius:25px;opacity:0.3;z-index:-1;}
        .stop-sign .stop-text{color:white;font-size:2rem;font-weight:bold;transform:rotate(-22.5deg);text-shadow:2px 2px 4px rgba(0,0,0,0.3);}
        @keyframes bounce{0%,20%,50%,80%,100%{transform:rotate(22.5deg) translateY(0);}40%{transform:rotate(22.5deg) translateY(-10px);}60%{transform:rotate(22.5deg) translateY(-5px);}}
        .error-code{font-size:8rem;font-weight:900;color:#dc3545;margin:1rem 0;text-shadow:3px 3px 6px rgba(0,0,0,0.1);line-height:1;}
        .error-title{font-size:3rem;color:#2c3e50;margin-bottom:1rem;font-weight:700;}
        .error-message{font-size:1.3rem;color:#6c757d;margin-bottom:3rem;line-height:1.6;max-width:600px;margin-left:auto;margin-right:auto;}
        .action-buttons{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;}
        .btn-home{background:linear-gradient(135deg,#28a745,#20c997);border:none;color:white;padding:15px 30px;border-radius:50px;font-size:1.1rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:0.5rem;transition:all 0.3s ease;box-shadow:0 4px 15px rgba(40,167,69,0.3);}
        .btn-home:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(40,167,69,0.4);color:white;text-decoration:none;}
        .security-guard{position:absolute;left:10%;bottom:10%;width:150px;height:200px;opacity:0.1;}
        .security-guard::before{content:'🚶‍♂️';font-size:8rem;position:absolute;animation:wave 3s ease-in-out infinite;}
        @keyframes wave{0%,100%{transform:rotate(0deg);}25%{transform:rotate(5deg);}75%{transform:rotate(-5deg);}}
        .floating-icons{position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;overflow:hidden;}
        .floating-icon{position:absolute;color:rgba(220,53,69,0.1);font-size:2rem;animation:float 6s ease-in-out infinite;}
        .floating-icon:nth-child(1){top:20%;left:10%;animation-delay:0s;}
        .floating-icon:nth-child(2){top:60%;left:80%;animation-delay:2s;}
        .floating-icon:nth-child(3){top:80%;left:20%;animation-delay:4s;}
        .floating-icon:nth-child(4){top:30%;left:70%;animation-delay:1s;}
        @keyframes float{0%,100%{transform:translateY(0px) rotate(0deg);}50%{transform:translateY(-20px) rotate(180deg);}}
        @media (max-width:768px){.error-code{font-size:5rem;}.error-title{font-size:2rem;}.error-message{font-size:1.1rem;padding:0 1rem;}.stop-sign{width:150px;height:150px;}.stop-sign .stop-text{font-size:1.5rem;}.action-buttons{flex-direction:column;align-items:center;}.btn-home{width:250px;justify-content:center;}.security-guard{display:none;}}
    </style>
</head>
<body>
    <div class="floating-icons">
        <i class="floating-icon fas fa-map-marker-alt"></i>
        <i class="floating-icon fas fa-compass"></i>
        <i class="floating-icon fas fa-route"></i>
        <i class="floating-icon fas fa-question-circle"></i>
    </div>

    <div class="security-guard"></div>

    <div class="error-container">
        <div class="stop-sign">
            <div class="stop-text">404</div>
        </div>

        <div class="error-code">404</div>

        <h1 class="error-title">Page Not Found</h1>

        <p class="error-message">
            Sorry, the page you're looking for doesn't exist.
            It might have been moved, deleted, or you entered the wrong URL.
        </p>

        <div class="action-buttons">
            <a href="{{ url('/') }}" class="btn-home">
                <i class="fas fa-home"></i>
                Go to Homepage
            </a>
        </div>
    </div>
</body>
</html>