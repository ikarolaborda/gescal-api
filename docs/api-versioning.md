# API Versioning Strategy

## Overview

The GESCAL API uses URI-based versioning to ensure backward compatibility while allowing for future enhancements and breaking changes.

## Versioning Scheme

**Current Version**: v1  
**Format**: `/api/{version}/{resource}`  
**Example**: `/api/v1/persons`, `/api/v2/persons`

## Supported Versions

### Version 1 (v1) - CURRENT
- **Status**: ✅ Active (Current Version)
- **Base Path**: `/api/v1/`
- **Features**: All current endpoints for persons, families, cases, benefits, and reference data
- **Stability**: Stable, production-ready
- **Support**: Fully supported

### Version 2 (v2) - PLANNED
- **Status**: 🚧 Future Version (Not Yet Available)
- **Base Path**: `/api/v2/`
- **Features**: TBD (will be announced when development begins)
- **Availability**: Future release

## Deprecation Policy

### Current Status

**V1 is NOT deprecated.** It is the current, active version of the API.

### Future Deprecation Process

When V1 is eventually deprecated (after V2 is released and stable), the following headers will be added to all V1 responses:

```http
Sunset: [Date when v1 will be discontinued]
Deprecation: [Date when v1 was marked deprecated]
Link: </api/v2>; rel="successor-version", <https://docs.example.com/api/migration-guide>; rel="deprecation"
X-API-Version: 1.0
X-API-Deprecated: true
X-API-Sunset-Info: Please migrate to /api/v2 before [sunset date]
```

### Current Headers

V1 responses currently include:

```http
X-API-Version: 1.0
X-API-Deprecated: false
```

### Lifecycle

1. **Active**: Version is current and fully supported
2. **Deprecated**: Version continues to work but is marked for retirement (headers added)
3. **Sunset**: Version is removed after sunset date (minimum 12 months notice)

## Migration Guide (v1 → v2)

### Timeline (Future)

| Phase | Date | Action |
|-------|------|--------|
| **Current** | **Now** | **V1 is active and fully supported** |
| V2 Development | TBD | V2 development begins |
| V2 Launch | TBD | V2 becomes available (V1 still active) |
| Deprecation Notice | TBD | V1 marked deprecated, headers added |
| Migration Period | 12 months | Both versions available |
| V1 Sunset | TBD + 12 months | V1 endpoints removed |

### Breaking Changes

V2 will introduce the following improvements (planned):

1. **Enhanced Error Responses**
   - More detailed error messages
   - Standardized error codes
   
2. **Improved Pagination**
   - Cursor-based pagination for better performance
   
3. **Additional Filters**
   - More query options for list endpoints

### Migration Steps

1. **Review Deprecation Headers**: Check your API responses for `Sunset` and `Deprecation` headers
2. **Test Against V2**: Once available, test your integration against `/api/v2/` endpoints
3. **Update Base URL**: Change your API client base URL from `/api/v1/` to `/api/v2/`
4. **Handle Breaking Changes**: Update your code to handle any breaking changes
5. **Monitor**: Ensure your application works correctly with v2
6. **Remove v1**: Remove all v1-specific code before the sunset date

## Best Practices

### For API Consumers

1. **Always specify version**: Never use unversioned endpoints
2. **Monitor headers**: Check for `Sunset` and `Deprecation` headers in responses
3. **Test early**: Start testing v2 as soon as it's available
4. **Update promptly**: Don't wait until the last minute to migrate

### For API Development

1. **Never break v1**: All changes to v1 must be backward compatible
2. **Document changes**: All v2 changes must be documented in migration guide
3. **Provide examples**: Include code examples for v2 endpoints
4. **Support period**: Maintain both versions for at least 12 months

## JSON:API Compliance

Both v1 and v2 follow the [JSON:API specification](https://jsonapi.org/) with the following requirements:

- `Content-Type: application/vnd.api+json`
- `Accept: application/vnd.api+json`
- Resource objects with `type`, `id`, `attributes`, `relationships`
- Proper error responses

## Authentication

Authentication remains consistent across versions:

- JWT tokens in `Authorization: Bearer {token}` header
- Same authentication endpoints across versions
- Tokens are version-agnostic

## Rate Limiting

Rate limits apply per-version:

- V1: Current rate limits apply
- V2: May have different rate limits (TBD)

## Support

For questions or issues related to API versioning:

- **Documentation**: Check this guide and OpenAPI specs
- **Issues**: Create a GitHub issue for bugs or feature requests
- **Migration Help**: Contact the development team

## Changelog

### v1.0 (Current)
- Initial release
- All CRUD operations for main resources
- Reference data endpoints with caching
- JWT authentication
- Role-based authorization

### v2.0 (Planned)
- TBD

---

**Last Updated**: 2025-11-22  
**Next Review**: TBD

