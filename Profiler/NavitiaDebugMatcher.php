<?php

namespace CanalTP\MttBundle\Profiler;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class NavitiaDebugMatcher implements RequestMatcherInterface
{
    protected $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function matches(Request $request)
    {
        return ($this->securityContext->getToken() != null && $this->securityContext->isGranted('BUSINESS_VIEW_NAVITIA_LOG'));
    }
}
